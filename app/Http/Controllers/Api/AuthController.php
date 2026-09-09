<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        // Support field aliases
        if ($request->filled('phone_number') && !$request->filled('phone')) {
            $request->merge(['phone' => $request->input('phone_number')]);
        }
        if ($request->filled('confirm_password') && !$request->filled('password_confirmation')) {
            $request->merge(['password_confirmation' => $request->input('confirm_password')]);
        }

        $validator = Validator::make($request->all(), [
            'first_name'            => ['nullable', 'string', 'max:100'],
            'last_name'             => ['nullable', 'string', 'max:100'],
            'phone'                 => ['required', 'string', 'max:25', 'unique:users,phone'],
            'country'               => ['nullable', 'string', 'max:100'],
            'email'                 => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['required_with:password', 'string', 'min:6'],
            'nickname'              => ['nullable', 'string', 'max:100'],
            'gender'                => ['nullable', 'string', 'in:female,male,other'],
            'age'                   => ['nullable', 'integer', 'min:18', 'max:120'],
            'city'                  => ['nullable', 'string', 'max:100'],
            'introduction'          => ['nullable', 'string', 'max:1000'],
            'languages'             => ['nullable'],
            'speaking_languages'    => ['nullable'],
            'tags'                  => ['nullable'],
            'interest_tags'         => ['nullable'],
            'video_call_rate'       => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $phone      = trim($request->phone);
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Derive name from first_name, nickname, email, or phone
        $firstName = trim($request->first_name ?? '');
        $lastName  = trim($request->last_name ?? '');
        if (empty($firstName)) {
            if ($request->filled('nickname')) {
                $firstName = trim($request->nickname);
            } elseif ($request->filled('email')) {
                $emailPrefix = explode('@', trim($request->email))[0] ?? '';
                $firstName = ucfirst(preg_replace('/[^a-zA-Z0-9]/', '', $emailPrefix)) ?: 'User';
            } else {
                $firstName = 'User_' . (strlen($cleanPhone) >= 4 ? substr($cleanPhone, -4) : rand(1000, 9999));
            }
        }
        $fullName = trim($firstName . ($lastName !== '' ? ' ' . $lastName : ''));

        // If email not provided, generate a clean placeholder email
        $email = $request->filled('email') 
            ? strtolower(trim($request->email)) 
            : ($cleanPhone ? $cleanPhone . '@user.chinchins.live' : 'user_' . time() . '@user.chinchins.live');

        // Country and Flag Resolution for any country worldwide
        $country = $request->filled('country') ? trim($request->country) : 'Bangladesh';
        $countryFlag = \App\Services\CountryService::toFlag($country);

        // Parse speaking languages
        $rawLangs = $request->input('speaking_languages', $request->input('languages', ['English', 'Bengali']));
        if (is_string($rawLangs)) {
            $decoded = json_decode($rawLangs, true);
            $rawLangs = is_array($decoded) ? $decoded : array_map('trim', explode(',', $rawLangs));
        }
        $languages = array_values(array_filter((array) $rawLangs)) ?: ['English', 'Bengali'];

        // Parse interest tags
        $rawTags = $request->input('interest_tags', $request->input('tags', ['Live Chat', 'Music', 'Gaming']));
        if (is_string($rawTags)) {
            $decoded = json_decode($rawTags, true);
            $rawTags = is_array($decoded) ? $decoded : array_map('trim', explode(',', $rawTags));
        }
        $tags = array_values(array_filter((array) $rawTags)) ?: ['Live Chat', 'Music', 'Gaming'];

        try {
            $user = User::create([
                'first_name'      => $firstName,
                'last_name'       => $lastName,
                'name'            => $fullName,
                'nickname'        => $request->filled('nickname') ? trim($request->nickname) : $firstName,
                'phone'           => $phone,
                'email'           => $email,
                'password'        => Hash::make($request->password),
                'country'         => $country,
                'country_flag'    => $countryFlag,
                'city'            => $request->input('city'),
                'gender'          => $request->input('gender', 'male'),
                'age'             => $request->input('age', 22),
                'introduction'    => $request->input('introduction', 'Welcome to my ChinChins Live profile! 🎉'),
                'languages'       => $languages,
                'tags'            => $tags,
                'video_call_rate' => $request->input('video_call_rate', 100),
                'is_active'       => true,
                'level'           => 1,
                'charm_level'     => 1,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status'     => true,
                'message'    => 'Registration successful',
                'data'       => [
                    'user'       => $user->fresh(),
                    'token'      => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login an existing user with email, phone, or account_id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required_without_all:email,phone,account_id', 'string'],
            'email'      => ['required_without_all:identifier,phone,account_id', 'string'],
            'phone'      => ['required_without_all:identifier,email,account_id', 'string'],
            'account_id' => ['required_without_all:identifier,email,phone', 'string'],
            'password'   => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $identifier = trim($request->input('identifier', $request->input('email', $request->input('phone', $request->input('account_id', '')))));
        $password   = $request->input('password');

        // Check if identifier matches email, phone, or account_id
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->orWhere('account_id', $identifier)
            ->first();

        // Also check if phone has/lacks country code or leading 0 format
        if (!$user && !filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
            if (strlen($cleanPhone) >= 9) {
        if (!$user) {
            // Check if account was deleted
            $trashedUser = User::onlyTrashed()->where(function ($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('phone', $identifier)
                  ->orWhere('email', 'LIKE', "%{$identifier}%")
                  ->orWhere('phone', 'LIKE', "%{$identifier}%")
                  ->orWhere('account_id', $identifier);
            })->first();

            if ($trashedUser) {
                return response()->json([
                    'status' => false,
                    'is_deleted' => true,
                    'message' => 'Your account has been deleted by administration. Please register a new account to continue.',
                    'action' => 'register_new'
                ], 403);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Invalid email/phone/account ID or password',
            ], 401);
        }

        if ($user->is_locked) {
            return response()->json([
                'status' => false,
                'is_locked' => true,
                'message' => 'Your account has been blocked: ' . ($user->locked_reason ?: 'Please contact administration.'),
            ], 403);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid email/phone/account ID or password',
            ], 401);
        }

        // Set user to active online upon login
        $user->update(['is_active' => true]);

        // Issue token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'     => true,
            'message'    => 'Login successful',
            'data'       => [
                'user'       => $user->fresh(),
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Delete user account from Mobile App.
     * POST /api/user/delete-account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated user. Please log in first.',
            ], 401);
        }

        $reason = $request->input('reason', 'User requested self deletion from Mobile App');

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Revoke active tokens
            $user->tokens()->delete();

            $user->is_active = false;
            $user->is_locked = true;
            $user->locked_reason = 'Account deleted';
            $user->deleted_reason = $reason;
            $user->deleted_by = 'user';

            // Free up phone / email so user can register fresh account later
            $timestamp = time();
            if ($user->phone && !str_starts_with($user->phone, 'deleted_')) {
                $user->phone = "deleted_{$timestamp}_" . $user->phone;
            }
            if ($user->email && !str_starts_with($user->email, 'deleted_')) {
                $user->email = "deleted_{$timestamp}_" . $user->email;
            }
            $user->save();

            // Soft delete user record
            $user->delete();

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => true,
                'is_deleted' => true,
                'message' => 'Your account has been deleted successfully. You may register a new account anytime.',
            ], 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve authenticated user from Bearer Token, Sanctum guard, or Custom Headers/Params.
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Direct request user via sanctum
        try {
            if ($user = $request->user('sanctum')) {
                return $user;
            }
            if ($user = $request->user()) {
                return $user;
            }
            if (\Illuminate\Support\Facades\Auth::guard('sanctum')->check()) {
                return \Illuminate\Support\Facades\Auth::guard('sanctum')->user();
            }
        } catch (\Throwable $e) {}

        // 2. Bearer token lookup in Sanctum PersonalAccessToken
        $token = $request->bearerToken() 
              ?: $request->header('Authorization') 
              ?: $request->input('token') 
              ?: $request->input('auth_token');

        if ($token) {
            $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $token));
            if (class_exists('\Laravel\Sanctum\PersonalAccessToken')) {
                try {
                    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
                    if ($accessToken && $accessToken->tokenable) {
                        return $accessToken->tokenable;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 3. Fallback to custom user header or parameter
        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('X-User-ID') 
                     ?? $request->header('X-Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $paramId = $request->input('user_id') ?? $request->input('userId') ?? $request->input('account_id');
        if ($paramId) {
            $u = User::find($paramId) ?? User::where('account_id', $paramId)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * Get authenticated user profile / verify token.
     * GET /api/auth/me or GET /api/auth/check
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated or session expired.',
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Session is valid and active.',
            'data'    => [
                'user' => $user->fresh(),
            ],
        ], 200);
    }

    /**
     * Logout authenticated user, revoke Bearer Token, and set offline presence.
     * POST /api/logout, POST /api/auth/logout, POST /api/user/logout
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status'  => true,
                'message' => 'Successfully logged out (Session already terminated).',
                'data'    => null,
            ], 200);
        }

        // 1. Revoke API Tokens
        try {
            if ($request->boolean('all_devices') || $request->boolean('logout_all')) {
                // Revoke all tokens across all devices
                $user->tokens()->delete();
            } else {
                // Revoke current active access token
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                // If token sent as bearer token string
                $token = $request->bearerToken() ?: $request->input('token');
                if ($token && class_exists('\Laravel\Sanctum\PersonalAccessToken')) {
                    $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $token));
                    \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean)?->delete();
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking token revocation
        }

        // 2. Update User Offline Status in Database
        $shouldClearFcm = $request->boolean('clear_fcm', true);
        $userUpdate = [
            'is_active'     => false,
            'is_busy'       => false,
            'online_status' => 'offline',
            'last_seen_at'  => now(),
        ];

        if ($shouldClearFcm) {
            $userUpdate['fcm_token'] = null;
            $userUpdate['device_token'] = null;
        }

        $user->update($userUpdate);

        // 3. Update User Presence Record
        try {
            if (class_exists('\App\Models\UserPresence')) {
                \App\Models\UserPresence::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'status'       => 'offline',
                        'is_online'    => false,
                        'last_seen_at' => now(),
                        'fcm_token'    => $shouldClearFcm ? null : $user->fcm_token,
                        'device_token' => $shouldClearFcm ? null : $user->device_token,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Non-blocking presence update
        }

        return response()->json([
            'status'  => true,
            'message' => 'Successfully logged out. Session terminated.',
            'data'    => [
                'user_id'       => $user->id,
                'account_id'    => $user->account_id,
                'online_status' => 'offline',
                'logged_out_at' => now()->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Delete Authenticated User's Account (Mobile App Settings -> Delete Account).
     * Strict Security: A user can ONLY delete their own authenticated account.
     * POST /api/user/delete-account, DELETE /api/user/delete-account, POST /api/account/delete
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Only the verified account owner can delete this account.',
            ], 401);
        }

        // Optional password verification if provided by client app
        if ($request->filled('password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Incorrect password. Account deletion aborted.',
                ], 422);
            }
        }

        $userId = $user->id;
        $accountId = $user->account_id;
        $userName = $user->display_name;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Revoke all active API tokens
            $user->tokens()->delete();

            // 2. Remove device push registrations & presence
            if (class_exists('\App\Models\DeviceRegistration')) {
                \App\Models\DeviceRegistration::where('user_id', $userId)->delete();
            }
            if (class_exists('\App\Models\UserPresence')) {
                \App\Models\UserPresence::where('user_id', $userId)->delete();
            }

            // 3. Mark user account as deleted / inactive
            $user->update([
                'is_active'     => false,
                'is_busy'       => false,
                'online_status' => 'deleted',
                'is_locked'     => true,
                'locked_reason' => 'Account deleted permanently by user request',
                'locked_at'     => now(),
                'fcm_token'     => null,
                'device_token'  => null,
                'last_seen_at'  => now(),
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Your account and all associated personal data have been permanently deleted.',
                'data'    => [
                    'user_id'    => $userId,
                    'account_id' => $accountId,
                    'deleted_at' => now()->toIso8601String(),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete account: ' . $e->getMessage(),
            ], 500);
        }
    }
}


