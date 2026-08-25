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
        $validator = Validator::make($request->all(), [
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'phone'           => ['required', 'string', 'max:25', 'unique:users,phone'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:6', 'confirmed'],
            'nickname'        => ['nullable', 'string', 'max:100'],
            'gender'          => ['nullable', 'string', 'in:female,male,other'],
            'age'             => ['nullable', 'integer', 'min:18', 'max:120'],
            'country'         => ['nullable', 'string', 'max:100'],
            'city'            => ['nullable', 'string', 'max:100'],
            'introduction'    => ['nullable', 'string', 'max:1000'],
            'languages'       => ['nullable', 'array'],
            'languages.*'     => ['string', 'max:50'],
            'tags'            => ['nullable', 'array'],
            'tags.*'          => ['string', 'max:50'],
            'video_call_rate' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $firstName = trim($request->first_name);
        $lastName  = trim($request->last_name);
        $fullName  = trim($firstName . ' ' . $lastName);

        $user = User::create([
            'first_name'      => $firstName,
            'last_name'       => $lastName,
            'name'            => $fullName,
            'nickname'        => $request->filled('nickname') ? trim($request->nickname) : $firstName,
            'phone'           => trim($request->phone),
            'email'           => strtolower(trim($request->email)),
            'password'        => Hash::make($request->password),
            'gender'          => $request->input('gender', 'female'),
            'age'             => $request->input('age', 27),
            'country'         => $request->input('country', 'Pakistan'),
            'city'            => $request->input('city'),
            'introduction'    => $request->input('introduction', 'Sweet girl looking for honest talk ❤️'),
            'languages'       => $request->input('languages', ['English', 'Urdu']),
            'tags'            => $request->input('tags', ['Live video', 'Music']),
            'video_call_rate' => $request->input('video_call_rate', 1800),
            'is_active'       => true,
            'level'           => 'Lv4',
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
                $user = User::where('phone', 'LIKE', '%' . substr($cleanPhone, -10))
                    ->orWhere('account_id', $cleanPhone)
                    ->first();
            }
        }

        if (!$user || !Hash::check($password, $user->password)) {
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
     * Get authenticated user profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data'   => [
                'user' => $request->user()->fresh(),
            ],
        ]);
    }

    /**
     * Logout authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->update(['is_active' => false]);
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Successfully logged out',
        ]);
    }
}
