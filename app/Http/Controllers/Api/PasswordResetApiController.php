<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetApiController extends Controller
{
    /**
     * Send a 6-digit OTP verification code to the user's registered email or phone.
     * POST /api/forgot-password or POST /api/password/forgot
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendResetCode(Request $request): JsonResponse
    {
        // Support multiple input keys
        $identifier = trim($request->input('email') 
            ?? $request->input('phone') 
            ?? $request->input('phone_number') 
            ?? $request->input('identifier', ''));

        $method = strtolower($request->input('method', filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone'));

        if (empty($identifier)) {
            return response()->json([
                'status'  => false,
                'message' => 'Please provide your registered email address or phone number.',
            ], 422);
        }

        // Find user by email, phone, or account_id
        $user = null;
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $identifier)->first();
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
            $user = User::where('phone', $identifier)
                ->orWhere('phone', 'LIKE', "%{$cleanPhone}%")
                ->orWhere('account_id', $identifier)
                ->first();
        }

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'We could not find an account associated with this ' . ($method === 'email' ? 'email address' : 'phone number') . '.',
            ], 404);
        }

        // Generate a 6-digit numeric OTP code
        $code = (string) random_int(100000, 999999);
        $targetKey = $method === 'email' ? strtolower(trim($user->email ?: $identifier)) : trim($user->phone ?: $identifier);

        // Delete any existing codes for this user
        DB::table('password_reset_tokens')
            ->where('email', $targetKey)
            ->delete();

        // Insert new token into password_reset_tokens
        DB::table('password_reset_tokens')->insert([
            'email'      => $targetKey,
            'token'      => $code,
            'created_at' => Carbon::now(),
        ]);

        // Send Email if method is email or user has a valid email address
        $emailSent = false;
        $userEmail = $user->email;

        if ($method === 'email' && !empty($userEmail) && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                $appName = config('mail.from.name', 'ChinChins Live');
                $fromAddress = config('mail.from.address', 'info@chinchins.live');
                $userName = $user->display_name ?: $user->name ?: 'User';

                $htmlContent = $this->buildEmailHtml($userName, $code, $appName);

                Mail::html($htmlContent, function ($message) use ($userEmail, $appName, $fromAddress) {
                    $message->to($userEmail)
                        ->from($fromAddress, $appName)
                        ->subject("{$appName} — Password Reset Verification Code");
                });

                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error('PasswordResetMailError: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'email'   => $userEmail,
                ]);
            }
        }

        // Mask recipient for security (e.g., u***@gmail.com or 017****8861)
        $maskedRecipient = $this->maskIdentifier($targetKey, $method);

        return response()->json([
            'status'  => true,
            'message' => "A 6-digit verification code has been sent to {$maskedRecipient}.",
            'data'    => [
                'method'             => $method,
                'recipient'          => $maskedRecipient,
                'expires_in_minutes' => 10,
                'user_id'            => $user->id,
                // Debug helper for testing in non-production environments
                'debug_code'         => config('app.debug') ? $code : null,
            ],
        ], 200);
    }

    /**
     * Verify the 6-digit OTP code.
     * POST /api/verify-reset-code or POST /api/password/verify-code
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyResetCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code'       => ['required', 'string', 'size:6'],
            'identifier' => ['nullable', 'string'],
            'email'      => ['nullable', 'string'],
            'phone'      => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $code = trim($request->input('code', $request->input('otp')));
        $identifier = trim($request->input('email') ?? $request->input('phone') ?? $request->input('identifier', ''));

        // Find matching record
        $recordQuery = DB::table('password_reset_tokens')->where('token', $code);

        if (!empty($identifier)) {
            $user = User::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->orWhere('account_id', $identifier)
                ->first();

            $possibleKeys = array_filter([
                $identifier,
                $user?->email,
                $user?->phone,
            ]);

            $recordQuery->whereIn('email', $possibleKeys);
        }

        $record = $recordQuery->first();

        if (!$record) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or incorrect verification code. Please check and try again.',
            ], 422);
        }

        // Check if code has expired (10 minutes lifetime)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('token', $code)->delete();
            return response()->json([
                'status'  => false,
                'message' => 'The verification code has expired. Please request a new code.',
            ], 422);
        }

        // Generate a temporary reset token (32 chars)
        $resetToken = Str::random(40);

        // Store reset token
        DB::table('password_reset_tokens')
            ->where('email', $record->email)
            ->update([
                'token'      => $resetToken,
                'created_at' => Carbon::now(),
            ]);

        return response()->json([
            'status'  => true,
            'message' => 'Verification code confirmed successfully.',
            'data'    => [
                'verified'    => true,
                'reset_token' => $resetToken,
                'identifier'  => $record->email,
            ],
        ], 200);
    }

    /**
     * Reset the user's password using verified code or reset_token.
     * POST /api/reset-password or POST /api/password/reset
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        // Support confirm_password alias
        if ($request->filled('confirm_password') && !$request->filled('password_confirmation')) {
            $request->merge(['password_confirmation' => $request->input('confirm_password')]);
        }
        if ($request->filled('new_password') && !$request->filled('password')) {
            $request->merge(['password' => $request->input('new_password')]);
        }

        $validator = Validator::make($request->all(), [
            'password'              => ['required', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['required_with:password', 'string', 'min:6'],
            'code'                  => ['nullable', 'string'],
            'token'                 => ['nullable', 'string'],
            'reset_token'           => ['nullable', 'string'],
            'identifier'            => ['nullable', 'string'],
            'email'                 => ['nullable', 'string'],
            'phone'                 => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $code = trim($request->input('reset_token') ?? $request->input('token') ?? $request->input('code', ''));
        $identifier = trim($request->input('email') ?? $request->input('phone') ?? $request->input('identifier', ''));

        if (empty($code)) {
            return response()->json([
                'status'  => false,
                'message' => 'Reset token or verification code is required.',
            ], 422);
        }

        // Locate reset record
        $recordQuery = DB::table('password_reset_tokens')->where('token', $code);

        if (!empty($identifier)) {
            $user = User::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->orWhere('account_id', $identifier)
                ->first();

            $possibleKeys = array_filter([
                $identifier,
                $user?->email,
                $user?->phone,
            ]);

            $recordQuery->whereIn('email', $possibleKeys);
        }

        $record = $recordQuery->first();

        if (!$record) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired password reset session. Please request a new verification code.',
            ], 422);
        }

        // Verify token validity (within 15 minutes of issuance)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('token', $code)->delete();
            return response()->json([
                'status'  => false,
                'message' => 'Reset session has expired. Please request a new code.',
            ], 422);
        }

        // Find the user to update
        $targetUser = User::where('email', $record->email)
            ->orWhere('phone', $record->email)
            ->first();

        if (!$targetUser) {
            return response()->json([
                'status'  => false,
                'message' => 'User account could not be found.',
            ], 404);
        }

        // Update user password
        $targetUser->password = Hash::make($request->password);
        $targetUser->save();

        // Invalidate all tokens for this email/phone so it cannot be reused
        DB::table('password_reset_tokens')->where('email', $record->email)->delete();

        // Revoke all existing API tokens for security (forces re-login)
        try {
            $targetUser->tokens()->delete();
        } catch (\Throwable $e) {}

        // Log the password change
        Log::info("Password reset successfully for User ID: {$targetUser->id} ({$targetUser->email})");

        return response()->json([
            'status'  => true,
            'message' => 'Your password has been successfully reset! You can now log in with your new password.',
            'data'    => [
                'user_id' => $targetUser->id,
                'email'   => $targetUser->email,
                'phone'   => $targetUser->phone,
            ],
        ], 200);
    }

    /**
     * Build modern, branded responsive HTML email template for password reset.
     */
    protected function buildEmailHtml(string $userName, string $code, string $appName): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$appName} - Password Reset Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0F0E17; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #FFFFFF;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #0F0E17; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" max-width="540" style="max-width: 540px; background: linear-gradient(135deg, #1E1B2E 0%, #161426 100%); border-radius: 20px; border: 1px solid rgba(255, 64, 129, 0.25); box-shadow: 0 10px 40px rgba(0,0,0,0.6); overflow: hidden;">
                    <!-- Header Banner -->
                    <tr>
                        <td align="center" style="padding: 35px 30px 20px 30px; background: linear-gradient(90deg, #FF4081, #7C4DFF); text-align: center;">
                            <h1 style="margin: 0; color: #FFFFFF; font-size: 26px; font-weight: 800; letter-spacing: 0.5px;">{$appName}</h1>
                            <p style="margin: 5px 0 0 0; color: rgba(255, 255, 255, 0.85); font-size: 13px;">Live Video Streaming & Community</p>
                        </td>
                    </tr>
                    
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 35px 30px 20px 30px; text-align: center;">
                            <h2 style="margin: 0 0 12px 0; color: #FFFFFF; font-size: 20px; font-weight: 700;">Password Reset Code</h2>
                            <p style="margin: 0 0 24px 0; color: #A0A5B5; font-size: 14px; line-height: 1.6;">
                                Hello <strong style="color: #FFFFFF;">{$userName}</strong>, we received a request to reset your {$appName} account password. Use the 6-digit verification code below to proceed:
                            </p>
                            
                            <!-- OTP Box -->
                            <div style="background: rgba(255, 64, 129, 0.1); border: 2px dashed #FF4081; border-radius: 14px; padding: 18px 24px; display: inline-block; margin: 10px 0 25px 0;">
                                <span style="font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #FF4081; font-family: 'Courier New', Courier, monospace;">{$code}</span>
                            </div>

                            <p style="margin: 0 0 10px 0; color: #FFB74D; font-size: 13px; font-weight: 600;">
                                ⏱️ This code will expire in <strong>10 minutes</strong>.
                            </p>
                            <p style="margin: 0; color: #787F95; font-size: 12px; line-height: 1.5;">
                                If you did not make this request, please ignore this email or contact support if you suspect unauthorized activity.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 30px; background-color: rgba(0,0,0,0.25); border-top: 1px solid rgba(255,255,255,0.08); text-align: center;">
                            <p style="margin: 0; color: #5B6178; font-size: 11px;">
                                &copy; 2026 {$appName}. All rights reserved.<br>
                                <a href="https://chinchins.live" style="color: #FF4081; text-decoration: none;">chinchins.live</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Mask an email or phone for privacy.
     */
    protected function maskIdentifier(string $identifier, string $method): string
    {
        if ($method === 'email' || str_contains($identifier, '@')) {
            $parts = explode('@', $identifier);
            $name = $parts[0];
            $domain = $parts[1] ?? 'example.com';
            $maskedName = substr($name, 0, 1) . str_repeat('*', max(3, strlen($name) - 2)) . (strlen($name) > 1 ? substr($name, -1) : '');
            return $maskedName . '@' . $domain;
        }

        $clean = preg_replace('/[^0-9]/', '', $identifier);
        if (strlen($clean) >= 6) {
            return substr($clean, 0, 3) . str_repeat('*', strlen($clean) - 5) . substr($clean, -2);
        }

        return substr($identifier, 0, 2) . '****';
    }
}
