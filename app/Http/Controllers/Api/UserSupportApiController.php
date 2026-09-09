<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAdminSupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserSupportApiController extends Controller
{
    /**
     * Resolve authenticated user from Bearer Token or headers.
     */
    protected function resolveUser(Request $request): ?User
    {
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

        try {
            if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()) {
                return Auth::guard('sanctum')->user();
            }
            if ($request->user('sanctum')) {
                return $request->user('sanctum');
            }
            if ($request->user()) {
                return $request->user();
            }
        } catch (\Throwable $e) {}

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

        $idParam = $request->input('user_id') ?? $request->input('userId') ?? $request->input('account_id');
        if ($idParam) {
            $u = User::find($idParam) ?? User::where('account_id', $idParam)->first();
            if ($u) return $u;
        }

        return User::first();
    }

    /**
     * Get User <-> Admin Live Support Messages.
     * GET /api/support/messages
     */
    public function getMessages(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // Mark unread admin messages as read
        UserAdminSupportMessage::where('user_id', $user->id)
            ->where('sender_type', 'admin')
            ->where('is_read_by_user', false)
            ->update(['is_read_by_user' => true, 'read_at' => now()]);

        $messages = UserAdminSupportMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($m) use ($user) {
                return [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'sender_type' => $m->sender_type, // 'user' or 'admin'
                    'is_me' => $m->sender_type === 'user',
                    'sender_name' => $m->sender_type === 'admin' ? 'ChinChins Official Support' : ($user->display_name ?: 'You'),
                    'type' => $m->type, // 'text', 'image', 'voice'
                    'message' => $m->message,
                    'media_url' => $m->full_media_url,
                    'is_read' => (bool) ($m->sender_type === 'user' ? $m->is_read_by_admin : $m->is_read_by_user),
                    'created_at' => $m->created_at ? $m->created_at->toIso8601String() : null,
                    'formatted_time' => $m->created_at ? $m->created_at->format('h:i A') : '',
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Support messages retrieved successfully.',
            'support_title' => 'Customer Service 24/7',
            'support_subtitle' => 'Official ChinChins Live Platform Support',
            'is_free' => true,
            'user' => [
                'id' => $user->id,
                'account_id' => $user->account_id,
                'name' => $user->display_name,
                'avatar' => $user->avatar_url,
                'coins' => (int) $user->coins,
            ],
            'data' => $messages,
        ], 200);
    }

    /**
     * Send message from User to Platform Admin Support.
     * POST /api/support/send
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $msgText = $request->input('message');
        $type = $request->input('type', 'text');
        $mediaUrl = null;

        // Handle Image / Screenshot Upload
        if ($request->hasFile('image') || $request->hasFile('file') || $request->hasFile('screenshot') || $request->hasFile('media')) {
            $file = $request->file('image') ?: ($request->file('file') ?: ($request->file('screenshot') ?: $request->file('media')));
            $filename = 'support_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/admin_support_for_user');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $mediaUrl = 'uploads/admin_support_for_user/' . $filename;
            $type = 'image';
        }

        // Handle Voice Note
        if ($request->hasFile('voice') || $request->hasFile('audio')) {
            $file = $request->file('voice') ?: $request->file('audio');
            $filename = 'voice_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/admin_support_for_user');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $mediaUrl = 'uploads/admin_support_for_user/' . $filename;
            $type = 'voice';
        }

        if (empty($msgText) && empty($mediaUrl)) {
            return response()->json(['status' => false, 'message' => 'Message or attachment is required.'], 422);
        }

        $msg = UserAdminSupportMessage::create([
            'user_id' => $user->id,
            'sender_type' => 'user',
            'type' => $type,
            'message' => $msgText,
            'media_url' => $mediaUrl,
            'is_read_by_admin' => false,
            'is_read_by_user' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent to Admin Support successfully.',
            'data' => [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'sender_type' => 'user',
                'is_me' => true,
                'sender_name' => $user->display_name,
                'type' => $msg->type,
                'message' => $msg->message,
                'media_url' => $msg->full_media_url,
                'is_read' => false,
                'created_at' => $msg->created_at->toIso8601String(),
                'formatted_time' => $msg->created_at->format('h:i A'),
            ]
        ], 201);
    }

    /**
     * Upload Image / Media attachment for Support.
     * POST /api/support/upload
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'file' => 'required|file|max:15360', // max 15MB
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $isAudio = in_array($ext, ['mp3', 'wav', 'aac', 'm4a', 'ogg']);
        $prefix = $isAudio ? 'voice_' : 'support_img_';

        $filename = $prefix . time() . '_' . Str::random(6) . '.' . $ext;
        $destDir = public_path('uploads/admin_support_for_user');
        if (!file_exists($destDir)) {
            @mkdir($destDir, 0777, true);
        }
        $file->move($destDir, $filename);
        $relativePath = 'uploads/admin_support_for_user/' . $filename;

        return response()->json([
            'status' => true,
            'message' => 'Attachment uploaded successfully.',
            'media_url' => asset($relativePath),
            'relative_path' => $relativePath,
            'type' => $isAudio ? 'voice' : 'image',
        ], 200);
    }

    /**
     * Get unread messages count from Admin.
     * GET /api/support/unread-count
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'count' => 0], 401);
        }

        $count = UserAdminSupportMessage::where('user_id', $user->id)
            ->where('sender_type', 'admin')
            ->where('is_read_by_user', false)
            ->count();

        return response()->json([
            'status' => true,
            'unread_count' => $count,
        ], 200);
    }
}
