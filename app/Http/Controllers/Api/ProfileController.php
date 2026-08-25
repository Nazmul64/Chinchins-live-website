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
     * Get profile by ID or Account ID, or current user.
     *
     * @param Request $request
     * @param string|null $id
     * @return JsonResponse
     */
    public function show(Request $request, ?string $id = null): JsonResponse
    {
        if ($id === null || $id === 'me') {
            $user = $request->user();
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
        $user = $request->user();

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
     * Upload single or multiple gallery photos.
     * The first uploaded image is automatically used as the cover photo if none is set.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPhotos(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'photos'   => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
            'photo'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $uploadedFiles = [];

        if ($request->hasFile('photo')) {
            $uploadedFiles[] = $request->file('photo');
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $uploadedFiles[] = $file;
                }
            }
        }

        if (empty($uploadedFiles)) {
            return response()->json([
                'status'  => false,
                'message' => 'No image files were provided for upload',
            ], 422);
        }

        $currentGallery = is_array($user->gallery_images) ? $user->gallery_images : [];
        $newStoredPaths = [];

        $storageDirectory = 'profiles/' . $user->id . '/gallery';

        foreach ($uploadedFiles as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($storageDirectory, $filename, 'public');
            $newStoredPaths[] = $path;
            $currentGallery[] = $path;
        }

        // Remove duplicates and keep clean array
        $currentGallery = array_values(array_unique($currentGallery));

        $updateData = [
            'gallery_images' => $currentGallery,
        ];

        // If cover_photo is not set or requested to set, default to the first image
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
            'message'    => count($uploadedFiles) . ' photo(s) uploaded successfully',
            'data'       => [
                'uploaded_paths' => $newStoredPaths,
                'user'           => $user->fresh(),
            ],
        ], 200);
    }

    /**
     * Upload dedicated Avatar photo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file = $request->file('avatar');
        $filename = 'avatar_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('profiles/' . $user->id, $filename, 'public');

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
                'avatar_url' => $user->avatar_url,
                'user'       => $user->fresh(),
            ],
        ]);
    }

    /**
     * Upload dedicated Cover Photo.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadCover(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'cover_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file = $request->file('cover_photo');
        $filename = 'cover_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('profiles/' . $user->id, $filename, 'public');

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
    }

    /**
     * Delete a photo from gallery.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'photo' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $photoTarget = trim($request->photo);
        // Normalize target (strip base URL if full URL was sent)
        $cleanTarget = str_replace(asset('storage') . '/', '', $photoTarget);
        $cleanTarget = ltrim($cleanTarget, '/');

        $currentGallery = is_array($user->gallery_images) ? $user->gallery_images : [];
        $updatedGallery = [];

        foreach ($currentGallery as $item) {
            $cleanItem = ltrim(str_replace(asset('storage') . '/', '', $item), '/');
            if ($cleanItem === $cleanTarget || $item === $photoTarget) {
                // Delete file from disk if stored locally
                if (Storage::disk('public')->exists($cleanItem)) {
                    Storage::disk('public')->delete($cleanItem);
                }
            } else {
                $updatedGallery[] = $item;
            }
        }

        $updateData = [
            'gallery_images' => array_values($updatedGallery),
        ];

        // If deleted cover photo, set to next available gallery image or null
        if ($user->cover_photo === $cleanTarget || $user->cover_photo === $photoTarget) {
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
    }

    /**
     * Toggle or set active online status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $user = $request->user();

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
                'is_active' => $user->is_active,
                'status'    => $user->is_active ? 'Active' : 'Offline',
                'user'      => $user->fresh(),
            ],
        ]);
    }
}
