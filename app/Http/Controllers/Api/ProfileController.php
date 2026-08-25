<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * Resolve the target user from Sanctum token, request user, or user_id/account_id fallback.
     *
     * @param Request $request
     * @return User|null
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Try Sanctum Bearer token
        if ($request->user('sanctum')) {
            return $request->user('sanctum');
        }

        // 2. Try default request user
        if ($request->user()) {
            return $request->user();
        }

        // 3. Try Authorization header Bearer token manually if needed
        $token = $request->bearerToken();
        if ($token) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable;
            }
        }

        // 4. Fallback: user_id, account_id, or phone in request body / query
        if ($request->filled('user_id')) {
            return User::find($request->user_id);
        }
        if ($request->filled('account_id')) {
            return User::where('account_id', $request->account_id)->first();
        }
        if ($request->filled('phone')) {
            return User::where('phone', $request->phone)->first();
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
                'message' => 'User profile not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'user' => $user->fresh(),
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

        $relativeDir = 'uploads/profiles/' . $userId . '/' . $folder;
        $targetDir = public_path($relativeDir);

        if (!file_exists($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        // 1. If it's an UploadedFile
        if ($input instanceof \Illuminate\Http\UploadedFile) {
            if (!$input->isValid()) {
                return null;
            }
            $ext = $input->getClientOriginalExtension() ?: 'jpg';
            $filename = $prefix . '_' . Str::uuid() . '.' . $ext;
            $input->move($targetDir, $filename);
            return $relativeDir . '/' . $filename;
        }

        // 2. If it's a Base64 string
        if (is_string($input) && (str_starts_with($input, 'data:image') || strlen($input) > 200)) {
            $data = $input;
            $ext = 'jpg';

            if (preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $data = substr($data, strpos($data, ',') + 1);
            }

            $decoded = base64_decode($data);
            if ($decoded !== false) {
                $filename = $prefix . '_' . Str::uuid() . '.' . $ext;
                $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
                @file_put_contents($fullPath, $decoded);
                return $relativeDir . '/' . $filename;
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

        // Clean domain and protocol
        $cleanPath = ltrim(str_replace([url('/'), asset('/')], '', $path), '/');

        // Check if inside public/uploads
        $publicFile = public_path($cleanPath);
        if (file_exists($publicFile) && is_file($publicFile)) {
            @unlink($publicFile);
            return;
        }

        // Check public/uploads prefixed
        if (!str_starts_with($cleanPath, 'uploads/')) {
            $withUploads = public_path('uploads/' . $cleanPath);
            if (file_exists($withUploads) && is_file($withUploads)) {
                @unlink($withUploads);
                return;
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
     * The first uploaded image is automatically used as the cover photo if none is set.
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

        // Also check base64 or string inputs
        if ($request->filled('photo_base64')) {
            $rawInputs[] = $request->input('photo_base64');
        }
        if ($request->filled('images_base64')) {
            $b64s = (array) $request->input('images_base64');
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

            $updateData = [
                'gallery_images' => $currentGallery,
            ];

            // If cover_photo is not set, default to the first image
            if (empty($user->cover_photo) && count($currentGallery) > 0) {
                $updateData['cover_photo'] = $currentGallery[0];
            }

            // If avatar is not set, set it to the first uploaded photo
            if (empty($user->avatar) && count($currentGallery) > 0) {
                $updateData['avatar'] = $currentGallery[0];
            }

            $user->update($updateData);

            return response()->json([
                'status'     => true,
                'message'    => count($newStoredPaths) . ' photo(s) uploaded successfully',
                'data'       => [
                    'uploaded_paths' => $newStoredPaths,
                    'user'           => $user->fresh(),
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
     * Supports both multipart image files and base64 strings.
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
              ?? $request->input('avatar_base64')
              ?? $request->input('image_base64')
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

            $currentGallery = is_array($user->gallery_images) ? $user->gallery_images : [];
            if (!in_array($path, $currentGallery)) {
                array_unshift($currentGallery, $path);
            }

            $user->update([
                'avatar'         => $path,
                'gallery_images' => $currentGallery,
                'cover_photo'    => $user->cover_photo ?: $path,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Avatar updated successfully',
                'data'    => [
                    'avatar_url'      => $user->avatar_url,
                    'profile_picture' => $user->avatar_url,
                    'user'            => $user->fresh(),
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
     * Delete Avatar photo.
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
                $user->update(['avatar' => null]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Avatar removed successfully',
                'data'    => [
                    'user' => $user->fresh(),
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

            return response()->json([
                'status'  => true,
                'message' => 'Cover photo updated successfully',
                'data'    => [
                    'cover_photo_url' => $user->cover_photo_url,
                    'user'            => $user->fresh(),
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
     * Delete Cover Photo.
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
                $user->update(['cover_photo' => null]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Cover photo removed successfully',
                'data'    => [
                    'user' => $user->fresh(),
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
     * Delete a single photo from gallery.
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

        $validator = Validator::make($request->all(), [
            'photo' => ['required_without:image', 'string'],
            'image' => ['required_without:photo', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Please provide the photo path or URL to delete.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $photoTarget = trim($request->input('photo', $request->input('image', '')));
            
            // Normalize target (strip base URL if full URL was sent)
            $cleanTarget = ltrim(str_replace([url('/'), asset('/')], '', $photoTarget), '/');

            $currentGallery = is_array($user->gallery_images) ? $user->gallery_images : [];
            $updatedGallery = [];

            foreach ($currentGallery as $item) {
                $cleanItem = ltrim(str_replace([url('/'), asset('/')], '', $item), '/');

                if ($cleanItem === $cleanTarget || $item === $photoTarget || basename($cleanItem) === basename($cleanTarget)) {
                    // Delete physical file from public/uploads
                    $this->deleteUploadedFile($cleanItem);
                } else {
                    $updatedGallery[] = $item;
                }
            }

            $updateData = [
                'gallery_images' => array_values($updatedGallery),
            ];

            // If deleted cover photo, set to next available gallery image or null
            $cleanCover = ltrim(str_replace([url('/'), asset('/')], '', $user->cover_photo ?? ''), '/');
            if ($cleanCover === $cleanTarget || $user->cover_photo === $photoTarget || basename($cleanCover) === basename($cleanTarget)) {
                $updateData['cover_photo'] = count($updatedGallery) > 0 ? $updatedGallery[0] : null;
            }

            $user->update($updateData);

            return response()->json([
                'status'  => true,
                'message' => 'Photo deleted successfully',
                'data'    => [
                    'user' => $user->fresh(),
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
                $clean = ltrim(str_replace([url('/'), asset('/')], '', $p), '/');
                $cleanList[] = $clean;
            }

            $user->update([
                'gallery_images' => array_values(array_unique($cleanList)),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Gallery updated successfully',
                'data'    => [
                    'user' => $user->fresh(),
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
                'cover_photo'    => null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'All gallery photos cleared',
                'data'    => [
                    'user' => $user->fresh(),
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
