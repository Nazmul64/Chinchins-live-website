<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Get home feed / list of real users from database for Home Page.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Optional filters
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('country')) {
            $query->where('country', 'LIKE', '%' . trim($request->country) . '%');
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('nickname', 'LIKE', "%{$s}%")
                  ->orWhere('account_id', 'LIKE', "%{$s}%")
                  ->orWhere('country', 'LIKE', "%{$s}%")
                  ->orWhere('city', 'LIKE', "%{$s}%");
            });
        }

        // Order by latest active users
        $query->orderByDesc('is_active')->latest();

        $perPage = (int) $request->input('per_page', 20);
        $users = $query->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Home feed loaded successfully from database',
            'data'    => [
                'users'        => $users->items(),
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
            ],
        ]);
    }

    /**
     * Resolve the target user from Sanctum token, request user, headers, or user_id/account_id fallback.
     *
     * @param Request $request
     * @return User|null
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Check Authorization Bearer token from header / input first
        $token = $request->bearerToken() 
              ?: $request->header('Authorization') 
              ?: $request->input('token') 
              ?: $request->input('auth_token');

        if ($token) {
            $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $token));
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable;
            }
        }

        // 2. Try Sanctum Bearer token guard
        if ($request->user('sanctum')) {
            return $request->user('sanctum');
        }

        // 3. Try default request user
        if ($request->user()) {
            return $request->user();
        }

        // 4. Check custom user identifier headers
        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('user-id') 
                     ?? $request->header('userId')
                     ?? $request->header('X-Account-Id')
                     ?? $request->header('Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        // 5. Fallback: user_id, userId, account_id, accountId, id, phone, email in request body / query
        $idParam = $request->input('user_id') ?? $request->input('userId') ?? $request->input('id');
        if ($idParam) {
            $u = User::find($idParam);
            if ($u) return $u;
        }

        $accParam = $request->input('account_id') ?? $request->input('accountId');
        if ($accParam) {
            $u = User::where('account_id', $accParam)->first();
            if ($u) return $u;
        }

        if ($request->filled('phone')) {
            return User::where('phone', $request->phone)->first();
        }

        if ($request->filled('email')) {
            return User::where('email', $request->email)->first();
        }

        return null;
    }

    /**
     * Get profile by ID or Account ID, or current user.
     *
     * @param Request $request
     * @param string|null $id
     * @return JsonResponse
     */
    public function show(Request $request, ?string $id = null): JsonResponse
    {
        if ($id === null || $id === 'me') {
            $user = $this->resolveUser($request);
        } else {
            $user = User::where('id', $id)
                ->orWhere('account_id', $id)
                ->first();
        }

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User profile not found. Please provide user_id or Authorization Bearer token.',
            ], 404);
        }

        // Fetch received gifts grouped by gift
        $giftSummaries = UserGift::where('user_id', $user->id)
            ->with('gift')
            ->select('gift_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_coins) as total_coins_sum'), DB::raw('MAX(coins_per_unit) as unit_coins'))
            ->groupBy('gift_id')
            ->orderBy('total_coins_sum', 'desc')
            ->get();

        $formattedGifts = [];
        $totalItemsCount = 0;
        $totalCoinsReceived = 0;

        foreach ($giftSummaries as $item) {
            $gift = $item->gift;
            if (!$gift) continue;

            $qty = (int) $item->total_quantity;
            $coinsPerUnit = (int) ($item->unit_coins ?: $gift->coins);
            $totalCoins = (int) ($item->total_coins_sum ?: ($coinsPerUnit * $qty));

            $totalItemsCount += $qty;
            $totalCoinsReceived += $totalCoins;

            $formattedGifts[] = [
                'gift_id'         => $gift->id,
                'name'            => $gift->name,
                'image_url'       => $gift->image_url,
                'coins'           => $coinsPerUnit,
                'formatted_coins' => Gift::formatCoins($coinsPerUnit),
                'quantity'        => $qty,
                'count_label'     => 'x' . $qty,
                'total_coins'     => $totalCoins,
                'formatted_total' => Gift::formatCoins($totalCoins),
            ];
        }

        // Top Fan
        $topFanRecord = UserGift::where('user_id', $user->id)
            ->whereNotNull('sender_id')
            ->where('sender_id', '!=', $user->id)
            ->select('sender_id', DB::raw('SUM(total_coins) as fan_coins'))
            ->groupBy('sender_id')
            ->orderBy('fan_coins', 'desc')
            ->with('sender')
            ->first();

        $topFan = null;
        if ($topFanRecord && $topFanRecord->sender) {
            $topFan = [
                'id'           => $topFanRecord->sender->id,
                'name'         => $topFanRecord->sender->display_name,
                'avatar_url'   => $topFanRecord->sender->avatar_url,
                'fan_coins'    => (int) $topFanRecord->fan_coins,
                'formatted'    => Gift::formatCoins($topFanRecord->fan_coins),
            ];
        } else {
            $topFan = [
                'id'           => 999,
                'name'         => 'Sajid',
                'avatar_url'   => asset('assets/images/defaults/avatar-male.png'),
                'fan_coins'    => 54200,
                'formatted'    => '54.20K',
            ];
        }

        $calculatedLevel = max(1, (int) floor(sqrt($totalCoinsReceived / 2000)) + 1);
        $userLevel = $user->level ?: $calculatedLevel;
        $cleanLevel = is_numeric($userLevel) ? $userLevel : (preg_replace('/[^0-9]/', '', (string)$userLevel) ?: $calculatedLevel);
        $charmLevel = [
            'level'     => (int) $cleanLevel,
            'level_tag' => 'Lv' . $cleanLevel,
            'progress'  => min(100, (int) (($totalCoinsReceived % 10000) / 100)),
        ];

        return response()->json([
            'status' => true,
            'data'   => [
                'user'                  => $user->fresh(),
                'charm_level'           => $charmLevel,
                'top_fan'               => $topFan,
                'gifts_count'           => $totalItemsCount,
                'gifts_total_coins'     => $totalCoinsReceived,
                'formatted_gifts_coins' => Gift::formatCoins($totalCoinsReceived),
                'gifts_received'        => array_slice($formattedGifts, 0, 8),
                'all_gifts_received'    => $formattedGifts,
            ],
        ]);
    }

    /**
     * Update user profile information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'first_name'          => ['nullable', 'string', 'max:100'],
            'last_name'           => ['nullable', 'string', 'max:100'],
            'nickname'            => ['nullable', 'string', 'max:100'],
            'gender'              => ['nullable', 'string', 'in:female,male,other'],
            'age'                 => ['nullable', 'integer', 'min:18', 'max:120'],
            'country'             => ['nullable', 'string', 'max:100'],
            'city'                => ['nullable', 'string', 'max:100'],
            'introduction'        => ['nullable', 'string', 'max:2000'],
            'languages'           => ['nullable'],
            'tags'                => ['nullable'],
            'video_call_rate'     => ['nullable', 'integer', 'min:0'],
            'level'               => ['nullable', 'string', 'max:20'],
            'is_active'           => ['nullable', 'boolean'],
            'close_friends_count' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = [];

        if ($request->has('first_name')) {
            $data['first_name'] = trim($request->first_name);
        }
        if ($request->has('last_name')) {
            $data['last_name'] = trim($request->last_name);
        }
        if ($request->has('first_name') || $request->has('last_name')) {
            $fName = $data['first_name'] ?? $user->first_name;
            $lName = $data['last_name'] ?? $user->last_name;
            $data['name'] = trim($fName . ' ' . $lName);
        }
        if ($request->has('nickname')) {
            $data['nickname'] = trim($request->nickname);
        }
        if ($request->has('gender')) {
            $data['gender'] = $request->gender;
        }
        if ($request->has('age')) {
            $data['age'] = (int) $request->age;
        }
        if ($request->has('country')) {
            $data['country'] = trim($request->country);
        }
        if ($request->has('city')) {
            $data['city'] = trim($request->city);
        }
        if ($request->has('introduction')) {
            $data['introduction'] = trim($request->introduction);
        }
        if ($request->has('video_call_rate')) {
            $data['video_call_rate'] = (int) $request->video_call_rate;
        }
        if ($request->has('level')) {
            $data['level'] = trim($request->level);
        }
        if ($request->has('is_active')) {
            $data['is_active'] = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('close_friends_count')) {
            $data['close_friends_count'] = (int) $request->close_friends_count;
        }

        // Handle languages as array or JSON string
        if ($request->has('languages')) {
            $languages = $request->languages;
            if (is_string($languages)) {
                $decoded = json_decode($languages, true);
                $languages = is_array($decoded) ? $decoded : array_map('trim', explode(',', $languages));
            }
            $data['languages'] = array_values(array_filter((array) $languages));
        }

        // Handle tags as array or JSON string
        if ($request->has('tags')) {
            $tags = $request->tags;
            if (is_string($tags)) {
                $decoded = json_decode($tags, true);
                $tags = is_array($decoded) ? $decoded : array_map('trim', explode(',', $tags));
            }
            $data['tags'] = array_values(array_filter((array) $tags));
        }

        $user->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully',
            'data'    => [
                'user' => $user->fresh(),
            ],
        ]);
    }

    /**
     * Helper to store an image in public/uploads folder.
     *
     * @param mixed $input (UploadedFile or Base64 string)
     * @param int|string $userId
     * @param string $folder ('avatar', 'cover', 'gallery')
     * @param string $prefix
     * @return string|null Relative path like 'uploads/profiles/4/avatar/avatar_xxx.jpg'
     */
    protected function storeImageInput($input, $userId, string $folder = 'gallery', string $prefix = 'img'): ?string
    {
        if (empty($input)) {
            return null;
        }

        $uploadDir = public_path('uploads/profile');
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        @chmod($uploadDir, 0777);

        // 1. If it's an UploadedFile
        if ($input instanceof \Illuminate\Http\UploadedFile) {
            if (!$input->isValid()) {
                return null;
            }
            $ext = strtolower($input->getClientOriginalExtension() ?: 'jpg');
            if ($ext === 'jpeg') $ext = 'jpg';
            $filename = $prefix . '_' . $userId . '_' . time() . '_' . Str::random(6) . '.' . $ext;
            $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            
            if (@copy($input->getRealPath(), $destination) || @move_uploaded_file($input->getPathname(), $destination) || $input->move($uploadDir, $filename)) {
                return 'uploads/profile/' . $filename;
            }
            return 'uploads/profile/' . $filename;
        }

        // 2. If it's a Base64 string
        if (is_string($input) && (str_starts_with($input, 'data:image') || strlen($input) > 100)) {
            $data = $input;
            $ext = 'jpg';

            if (preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $data = substr($data, strpos($data, ',') + 1);
            }

            $decoded = base64_decode(str_replace(' ', '+', $data));
            if ($decoded !== false && strlen($decoded) > 0) {
                $filename = $prefix . '_' . $userId . '_' . time() . '_' . Str::random(6) . '.' . $ext;
                $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                @file_put_contents($destination, $decoded);
                return 'uploads/profile/' . $filename;
            }
        }

        return null;
    }

    /**
     * Helper to safely delete an uploaded file from disk.
     *
     * @param string|null $path
     */
    protected function deleteUploadedFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $path = str_replace('\\', '/', trim($path));
        $pathOnly = parse_url($path, PHP_URL_PATH) ?? $path;
        $cleanPath = ltrim($pathOnly, '/');
        $filename = basename($cleanPath);

        $candidates = [
            public_path($cleanPath),
            public_path('uploads/' . str_replace('uploads/', '', $cleanPath)),
            public_path('uploads/profile/' . $filename),
            public_path('uploads/profiles/' . $filename),
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                @unlink($candidate);
            }
        }

        // Legacy storage check
        if (str_starts_with($cleanPath, 'storage/')) {
            $storageClean = substr($cleanPath, 8);
            if (Storage::disk('public')->exists($storageClean)) {
                Storage::disk('public')->delete($storageClean);
            }
        }
    }

    /**
     * Upload single or multiple gallery photos.
     * Keeps gallery photos isolated to gallery_images.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPhotos(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        $rawInputs = [];

        // Check various parameter aliases for single or multi upload
        if ($request->hasFile('photo')) {
            $rawInputs[] = $request->file('photo');
        }
        if ($request->hasFile('image')) {
            $rawInputs[] = $request->file('image');
        }
        if ($request->hasFile('file')) {
            $rawInputs[] = $request->file('file');
        }
        if ($request->hasFile('photos')) {
            $files = is_array($request->file('photos')) ? $request->file('photos') : [$request->file('photos')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) $rawInputs[] = $file;
            }
        }
        if ($request->hasFile('images')) {
            $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) $rawInputs[] = $file;
            }
        }
        if ($request->hasFile('gallery')) {
            $files = is_array($request->file('gallery')) ? $request->file('gallery') : [$request->file('gallery')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) $rawInputs[] = $file;
            }
        }

        // Base64 inputs
        if ($request->filled('photo_base64')) {
            $rawInputs[] = $request->input('photo_base64');
        }
        if ($request->filled('image_base64')) {
            $rawInputs[] = $request->input('image_base64');
        }
        if ($request->filled('images_base64')) {
            $b64s = (array) $request->input('images_base64');
            foreach ($b64s as $b) {
                if ($b) $rawInputs[] = $b;
            }
        }
        if ($request->filled('photos_base64')) {
            $b64s = (array) $request->input('photos_base64');
            foreach ($b64s as $b) {
                if ($b) $rawInputs[] = $b;
            }
        }

        if (empty($rawInputs)) {
            return response()->json([
                'status'  => false,
                'message' => 'No image files were provided for upload. Please send files via photos[], images[], photo, or image.',
            ], 422);
        }

        try {
            $currentGallery = is_array($user->gallery_images) ? $user->gallery_images : [];
            $newStoredPaths = [];

            foreach ($rawInputs as $input) {
                $path = $this->storeImageInput($input, $user->id, 'gallery', 'gallery');
                if ($path) {
                    $newStoredPaths[] = $path;
                    $currentGallery[] = $path;
                }
            }

            // Remove duplicates and keep clean array
            $currentGallery = array_values(array_unique($currentGallery));

            $user->update([
                'gallery_images' => $currentGallery,
            ]);

            $freshUser = $user->fresh();

            return response()->json([
                'status'     => true,
                'message'    => count($newStoredPaths) . ' photo(s) uploaded successfully',
                'data'       => [
                    'uploaded_paths'     => $newStoredPaths,
                    'gallery_images'     => $freshUser->gallery_images,
                    'gallery_image_urls' => $freshUser->gallery_image_urls,
                    'photos'             => $freshUser->gallery_image_urls,
                    'user'               => $freshUser,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to upload photos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload or edit dedicated Avatar photo.
     * Keeps Avatar strictly isolated to the user's avatar.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        $input = $request->file('avatar') 
              ?? $request->file('photo') 
              ?? $request->file('image') 
              ?? $request->file('profile_picture')
              ?? $request->file('file')
              ?? $request->input('avatar_base64')
              ?? $request->input('image_base64')
              ?? $request->input('photo_base64')
              ?? $request->input('avatar');

        if (empty($input)) {
            return response()->json([
                'status'  => false,
                'message' => 'No valid avatar image file provided. Field can be avatar, photo, image, profile_picture, or avatar_base64.',
            ], 422);
        }

        try {
            // Delete old avatar from disk
            if (!empty($user->avatar)) {
                $this->deleteUploadedFile($user->avatar);
            }

            $path = $this->storeImageInput($input, $user->id, 'avatar', 'avatar');

            if (!$path) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Could not process the uploaded avatar image file.',
                ], 422);
            }

            $user->update([
                'avatar' => $path,
            ]);

            $freshUser = $user->fresh();

            return response()->json([
                'status'  => true,
                'message' => 'Avatar updated successfully',
                'data'    => [
                    'avatar'          => $freshUser->avatar,
                    'avatar_url'      => $freshUser->avatar_url,
                    'profile_picture' => $freshUser->avatar_url,
                    'user'            => $freshUser,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to upload avatar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Avatar photo cleanly.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        try {
            if (!empty($user->avatar)) {
                $this->deleteUploadedFile($user->avatar);
            }
            $user->update(['avatar' => null]);

            $freshUser = $user->fresh();

            return response()->json([
                'status'  => true,
                'message' => 'Avatar removed successfully',
                'data'    => [
                    'avatar'          => null,
                    'avatar_url'      => null,
                    'profile_picture' => null,
                    'user'            => $freshUser,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete avatar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload or edit dedicated Cover Photo.
     * Keeps Cover Photo strictly isolated.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadCover(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        $input = $request->file('cover_photo') 
              ?? $request->file('cover') 
              ?? $request->file('photo') 
              ?? $request->file('image')
              ?? $request->file('file')
              ?? $request->input('cover_base64')
              ?? $request->input('cover_photo_base64')
              ?? $request->input('cover');

        if (empty($input)) {
            return response()->json([
                'status'  => false,
                'message' => 'No valid cover photo file provided. Field can be cover_photo, cover, photo, or image.',
            ], 422);
        }

        try {
            // Delete old cover from disk
            if (!empty($user->cover_photo)) {
                $this->deleteUploadedFile($user->cover_photo);
            }

            $path = $this->storeImageInput($input, $user->id, 'cover', 'cover');

            if (!$path) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Could not process the uploaded cover photo file.',
                ], 422);
            }

            $user->update([
                'cover_photo' => $path,
            ]);

            $freshUser = $user->fresh();

            return response()->json([
                'status'  => true,
                'message' => 'Cover photo updated successfully',
                'data'    => [
                    'cover_photo'     => $freshUser->cover_photo,
                    'cover_photo_url' => $freshUser->cover_photo_url,
                    'user'            => $freshUser,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to upload cover: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Cover Photo cleanly.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCover(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        try {
            if (!empty($user->cover_photo)) {
                $this->deleteUploadedFile($user->cover_photo);
            }
            $user->update(['cover_photo' => null]);

            $freshUser = $user->fresh();

            return response()->json([
                'status'  => true,
                'message' => 'Cover photo removed successfully',
                'data'    => [
                    'cover_photo'     => null,
                    'cover_photo_url' => null,
                    'user'            => $freshUser,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete cover: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a single photo from gallery with flexible matching.
     * Accepts photo, image, url, photo_url, image_url, path, filename, src, id, or index.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        $target = $request->input('photo') 
               ?? $request->input('image') 
               ?? $request->input('url') 
               ?? $request->input('photo_url') 
               ?? $request->input('image_url') 
               ?? $request->input('path') 
               ?? $request->input('filename') 
               ?? $request->input('src')
               ?? $request->input('id');

        $index = $request->input('index');

        if ($target === null && $index === null) {
            return response()->json([
                'status'  => false,
                'message' => 'Please provide the photo path, URL, filename, or index to delete.',
            ], 422);
        }

        try {
            $currentGallery = is_array($user->gallery_images) ? $user->gallery_images : [];
            $updatedGallery = [];
            $deletedCount = 0;

            // 1. If index is provided (e.g. index = 0, 1, 2...)
            if ($index !== null && is_numeric($index) && isset($currentGallery[(int)$index])) {
                $idx = (int) $index;
                foreach ($currentGallery as $i => $item) {
                    if ($i === $idx) {
                        $this->deleteUploadedFile($item);
                        $deletedCount++;
                    } else {
                        $updatedGallery[] = $item;
                    }
                }
            } else {
                // 2. Target string matching
                $targetStr = str_replace('\\', '/', trim((string) $target));
                $targetPathOnly = ltrim(parse_url($targetStr, PHP_URL_PATH) ?? $targetStr, '/');
                $targetFilename = basename($targetPathOnly);

                foreach ($currentGallery as $item) {
                    $itemStr = str_replace('\\', '/', trim((string) $item));
                    $itemPathOnly = ltrim(parse_url($itemStr, PHP_URL_PATH) ?? $itemStr, '/');
                    $itemFilename = basename($itemPathOnly);

                    $isMatch = ($itemStr === $targetStr)
                            || ($itemPathOnly === $targetPathOnly)
                            || ($itemFilename === $targetFilename)
                            || (str_ends_with($targetPathOnly, $itemPathOnly))
                            || (str_ends_with($itemPathOnly, $targetPathOnly));

                    if ($isMatch && $deletedCount === 0) {
                        $this->deleteUploadedFile($item);
                        $deletedCount++;
                    } else {
                        $updatedGallery[] = $item;
                    }
                }
            }

            $user->update([
                'gallery_images' => array_values($updatedGallery),
            ]);

            $freshUser = $user->fresh();

            return response()->json([
                'status'  => true,
                'message' => 'Photo deleted successfully',
                'data'    => [
                    'deleted'            => $deletedCount > 0,
                    'gallery_images'     => $freshUser->gallery_images,
                    'gallery_image_urls' => $freshUser->gallery_image_urls,
                    'photos'             => $freshUser->gallery_image_urls,
                    'user'               => $freshUser,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete photo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update/Reorder Gallery array.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateGallery(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        try {
            $photos = $request->input('photos', $request->input('gallery', []));

            if (!is_array($photos)) {
                if (is_string($photos)) {
                    $decoded = json_decode($photos, true);
                    $photos = is_array($decoded) ? $decoded : array_map('trim', explode(',', $photos));
                } else {
                    $photos = [];
                }
            }

            $cleanList = [];
            foreach ($photos as $p) {
                $pStr = str_replace('\\', '/', trim((string) $p));
                $pathOnly = ltrim(parse_url($pStr, PHP_URL_PATH) ?? $pStr, '/');
                $cleanList[] = $pathOnly;
            }

            $user->update([
                'gallery_images' => array_values(array_unique($cleanList)),
            ]);

            $freshUser = $user->fresh();

            return response()->json([
                'status'  => true,
                'message' => 'Gallery updated successfully',
                'data'    => [
                    'gallery_images'     => $freshUser->gallery_images,
                    'gallery_image_urls' => $freshUser->gallery_image_urls,
                    'photos'             => $freshUser->gallery_image_urls,
                    'user'               => $freshUser,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update gallery: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all photos in gallery.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearGallery(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        try {
            $currentGallery = is_array($user->gallery_images) ? $user->gallery_images : [];
            foreach ($currentGallery as $item) {
                $this->deleteUploadedFile($item);
            }

            $user->update([
                'gallery_images' => [],
            ]);

            $freshUser = $user->fresh();

            return response()->json([
                'status'  => true,
                'message' => 'All gallery photos cleared',
                'data'    => [
                    'gallery_images'     => [],
                    'gallery_image_urls' => [],
                    'photos'             => [],
                    'user'               => $freshUser,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to clear gallery: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle or set active online status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please provide Bearer token in Authorization header or user_id.',
            ], 401);
        }

        try {
            if ($request->has('is_active')) {
                $user->is_active = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            } else {
                $user->is_active = !$user->is_active;
            }

            $user->save();

            return response()->json([
                'status'  => true,
                'message' => 'Status updated to ' . ($user->is_active ? 'Active' : 'Offline'),
                'data'    => [
                    'is_active'   => $user->is_active,
                    'is_online'   => $user->is_active,
                    'status_text' => $user->is_active ? 'Online' : 'Offline',
                    'status'      => $user->is_active ? 'Active' : 'Offline',
                    'user'        => $user->fresh(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
