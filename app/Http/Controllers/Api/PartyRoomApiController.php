<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\PartyRoom;
use App\Models\PartyRoomMember;
use App\Models\PartyRoomMessage;
use App\Models\PartyRoomSeat;
use App\Models\PartyRoomSeatInvitation;
use App\Models\PartyRoomSetting;
use App\Models\StreamingSetting;
use App\Models\User;
use App\Models\UserLike;
use App\Services\Calling\CallingManager;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PartyRoomApiController extends Controller
{
    protected CallingManager $callingManager;

    public function __construct(CallingManager $callingManager)
    {
        $this->callingManager = $callingManager;
    }

    /**
     * Resilient User Resolver across Bearer token, Sanctum, Headers, and Request params.
     */
    protected function resolveUser(Request $request): ?User
    {
        try {
            if ($user = $request->user('sanctum')) {
                return $user;
            }
            if ($user = $request->user()) {
                return $user;
            }
            if (Auth::guard('sanctum')->check()) {
                return Auth::guard('sanctum')->user();
            }
        } catch (\Throwable $e) {}

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

        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('X-User-ID') 
                     ?? $request->header('X-Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $paramId = $request->input('user_id') ?? $request->input('userId') ?? $request->input('uid') ?? $request->input('account_id');
        if ($paramId) {
            $u = User::find($paramId) ?? User::where('account_id', $paramId)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * Get Party Room Configuration & Topic Tags.
     * GET /api/party-rooms/config
     */
    public function getConfig(Request $request): JsonResponse
    {
        $settings = PartyRoomSetting::getSettings();

        return response()->json([
            'success' => true,
            'status' => true,
            'data' => [
                'is_enabled' => (bool) $settings->is_party_room_enabled,
                'default_voice_rate' => (int) $settings->default_voice_rate_per_minute,
                'default_video_rate' => (int) $settings->default_video_rate_per_minute,
                'host_commission_percentage' => (float) $settings->host_commission_percentage,
                'admin_commission_percentage' => (float) $settings->admin_commission_percentage,
                'max_guests' => (int) $settings->max_guests_per_room,
                'topic_tags' => $settings->available_topic_tags ?: [
                    ['id' => 'singing', 'name' => 'Singing 🎤', 'tag' => 'Singing'],
                    ['id' => 'dating', 'name' => 'Dating ❤️', 'tag' => 'Dating'],
                    ['id' => 'party', 'name' => 'Party 💃', 'tag' => 'Party'],
                    ['id' => 'chitchat', 'name' => 'ChitChat 💬', 'tag' => 'ChitChat'],
                    ['id' => 'gaming', 'name' => 'Gaming 🎮', 'tag' => 'Gaming'],
                    ['id' => 'latenight', 'name' => 'Late Night 🌙', 'tag' => 'Late Night'],
                ],
                'default_announcement' => $settings->default_announcement,
            ],
        ]);
    }

    /**
     * Browse Active Party Rooms (Live Feed).
     * GET /api/party-rooms
     * Query: room_type (voice/video), topic_tag, search, page
     */
    public function index(Request $request): JsonResponse
    {
        $query = PartyRoom::with(['host', 'activeSeats.user'])
            ->where('status', 'active');

        if ($request->filled('room_type') && in_array($request->room_type, ['voice', 'video'])) {
            $query->where('room_type', $request->room_type);
        }

        if ($request->filled('topic_tag')) {
            $query->where('topic_tag', 'like', '%' . trim($request->topic_tag) . '%');
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('room_title', 'like', "%{$search}%")
                  ->orWhere('room_id', 'like', "%{$search}%")
                  ->orWhereHas('host', function ($hq) use ($search) {
                      $hq->where('name', 'like', "%{$search}%")
                         ->orWhere('display_name', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  });
            });
        }

        $rooms = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 20));

        $data = $rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'room_id' => $room->room_id,
                'room_title' => $room->room_title,
                'room_type' => $room->room_type,
                'topic_tag' => $room->topic_tag,
                'room_cover' => $room->room_cover_url,
                'background_image' => $room->background_image_url,
                'channel_name' => $room->channel_name,
                'max_seats' => $room->max_seats,
                'occupied_seats' => $room->occupied_seats_count,
                'online_members' => $room->online_members_count,
                'coin_rate_per_minute' => $room->coin_rate_per_minute,
                'is_locked' => (bool) $room->is_locked,
                'status' => $room->status,
                'host' => [
                    'id' => $room->host?->id,
                    'account_id' => $room->host?->account_id,
                    'name' => $room->host?->display_name ?? $room->host?->name ?? 'Host',
                    'avatar_url' => $room->host?->avatar_url,
                    'avatar_frame_url' => $room->host?->avatar_frame_url,
                    'level' => (int) ($room->host?->level ?? 1),
                ],
                'created_at' => $room->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'status' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
                'total' => $rooms->total(),
            ],
        ]);
    }

    /**
     * Create / Host a New Party Room (Voice Party or Video Party).
     * POST /api/party-rooms/create
     */
    public function create(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized. Please authenticate first.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'room_title' => 'nullable|string|max:150',
            'room_type' => 'required|in:voice,video',
            'topic_tag' => 'nullable|string|max:64',
            'room_cover' => 'nullable',
            'room_cover_file' => 'nullable|image|max:10240',
            'max_seats' => 'nullable|integer|min:4|max:12',
            'coin_rate_per_minute' => 'nullable|integer|min:0',
            'announcement' => 'nullable|string|max:500',
            'password' => 'nullable|string|max:32',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user already has an active room, close/reuse it
        PartyRoom::where('host_id', $user->id)
            ->where('status', 'active')
            ->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);

        $settings = PartyRoomSetting::getSettings();
        $roomId = PartyRoom::generateRoomId();
        $channelName = 'party_' . strtolower($request->room_type) . '_' . strtolower($roomId);

        // Handle Image Upload for Room Cover (saved to public/uploads/host_image/)
        $coverPath = null;
        if ($request->hasFile('room_cover_file') || $request->hasFile('room_cover')) {
            $file = $request->file('room_cover_file') ?: $request->file('room_cover');
            if ($file && $file->isValid()) {
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = 'party_cover_' . time() . '_' . Str::random(8) . '.' . $ext;
                $file->move(public_path('uploads/host_image'), $filename);
                $coverPath = 'uploads/host_image/' . $filename;
            }
        } elseif ($request->filled('room_cover') && is_string($request->room_cover)) {
            $coverPath = $request->room_cover;
        }

        $rate = $request->filled('coin_rate_per_minute') 
            ? (int) $request->coin_rate_per_minute 
            : ($request->room_type === 'video' ? $settings->default_video_rate_per_minute : $settings->default_voice_rate_per_minute);

        $room = PartyRoom::create([
            'room_id' => $roomId,
            'host_id' => $user->id,
            'room_title' => trim($request->input('room_title') ?: 'My Live Fun Hangout 🥳✨'),
            'room_type' => $request->input('room_type', 'voice'),
            'topic_tag' => trim($request->input('topic_tag') ?: 'Singing'),
            'room_cover' => $coverPath,
            'channel_name' => $channelName,
            'max_seats' => (int) ($request->input('max_seats') ?: $settings->max_guests_per_room ?: 10),
            'coin_rate_per_minute' => $rate,
            'host_commission_percentage' => (float) $settings->host_commission_percentage,
            'admin_commission_percentage' => (float) $settings->admin_commission_percentage,
            'status' => 'active',
            'is_locked' => !empty($request->password),
            'room_password' => $request->password,
            'announcement' => $request->input('announcement') ?: $settings->default_announcement,
            'started_at' => now(),
        ]);

        // Initialize 10 seats (Seat 1 = Host, Seats 2..10 = Guests)
        $room->initializeSeats();

        // Add Host as Active Member
        PartyRoomMember::updateOrCreate(
            ['party_room_id' => $room->id, 'user_id' => $user->id],
            ['role' => 'host', 'status' => 'active', 'last_active_at' => now()]
        );

        // Post Welcome Announcement System Message
        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id' => $user->id,
            'type' => 'system',
            'message' => '📢 ' . ($room->announcement ?: 'Welcome to My Live Fun Hangout 🥳✨!'),
        ]);

        // Generate RTC Streaming Token for Host
        $rtcCredentials = $this->callingManager->initializeSession(
            $user,
            $room->channel_name,
            $room->room_type,
            'host'
        );

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Party room created successfully!',
            'data' => [
                'room' => $this->formatRoomDetails($room, $user),
                'rtc' => $rtcCredentials,
            ],
        ], 201);
    }

    /**
     * Get Detailed Room State (10 Seats Grid, Host Info, Announcement, Audience, Token).
     * GET /api/party-rooms/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        $room = PartyRoom::where('id', $id)
            ->orWhere('room_id', $id)
            ->orWhere('channel_name', $id)
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Party room not found.',
            ], 404);
        }

        // Determine User RTC Role (Publisher if in seat 1..10, Audience/Subscriber otherwise)
        $role = 'audience';
        if ($user) {
            $isOccupyingSeat = $room->seats()->where('user_id', $user->id)->where('status', 'occupied')->exists();
            if ($user->id === $room->host_id || $isOccupyingSeat) {
                $role = ($user->id === $room->host_id) ? 'host' : 'publisher';
            }
        }

        $rtcCredentials = null;
        if ($user) {
            $rtcCredentials = $this->callingManager->initializeSession(
                $user,
                $room->channel_name,
                $room->room_type,
                $role
            );
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'data' => [
                'room' => $this->formatRoomDetails($room, $user),
                'rtc' => $rtcCredentials,
            ],
        ]);
    }

    /**
     * Join Room as Audience / Viewer.
     * POST /api/party-rooms/{id}/join
     */
    public function join(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room || $room->status !== 'active') {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'This party room is no longer active.',
            ], 404);
        }

        // Check password if room is locked
        if ($room->is_locked && $user->id !== $room->host_id) {
            $pwd = $request->input('password');
            if ($pwd !== $room->room_password) {
                return response()->json([
                    'success' => false,
                    'status' => false,
                    'message' => 'Incorrect room password.',
                    'is_locked' => true,
                ], 403);
            }
        }

        // Register / update audience member
        PartyRoomMember::updateOrCreate(
            ['party_room_id' => $room->id, 'user_id' => $user->id],
            [
                'role' => ($user->id === $room->host_id) ? 'host' : 'audience',
                'status' => 'active',
                'last_active_at' => now(),
            ]
        );

        // System broadcast message in chat
        if ($user->id !== $room->host_id) {
            PartyRoomMessage::create([
                'party_room_id' => $room->id,
                'user_id' => $user->id,
                'type' => 'system',
                'message' => '👋 ' . ($user->display_name ?? $user->name) . ' joined the party room!',
            ]);
        }

        $rtcCredentials = $this->callingManager->initializeSession(
            $user,
            $room->channel_name,
            $room->room_type,
            ($user->id === $room->host_id) ? 'host' : 'audience'
        );

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Joined party room successfully.',
            'data' => [
                'room' => $this->formatRoomDetails($room, $user),
                'rtc' => $rtcCredentials,
            ],
        ]);
    }

    /**
     * Leave Party Room.
     * POST /api/party-rooms/{id}/leave
     */
    public function leave(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found.'], 404);
        }

        // If Host leaves, host can choose to end or keep room open
        if ($user->id === $room->host_id && $request->boolean('end_room', false)) {
            $room->update(['status' => 'ended', 'ended_at' => now()]);
            $room->seats()->update(['status' => 'empty', 'user_id' => null]);
            return response()->json(['success' => true, 'message' => 'Party room ended by host.']);
        }

        // If user was on a seat (2..10), vacate seat
        $room->seats()
            ->where('user_id', $user->id)
            ->where('seat_index', '>', 1)
            ->update([
                'user_id' => null,
                'status' => 'empty',
                'is_muted' => false,
                'is_video_muted' => false,
            ]);

        // Mark member left
        PartyRoomMember::where('party_room_id', $room->id)
            ->where('user_id', $user->id)
            ->update(['status' => 'left', 'left_at' => now()]);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Left party room successfully.',
        ]);
    }

    /**
     * End Party Room (Host Only).
     * POST /api/party-rooms/{id}/end
     */
    public function endRoom(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found.'], 404);
        }

        if ($user->id !== $room->host_id && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the room host can end the room.'], 403);
        }

        $room->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        $room->seats()->update(['status' => 'empty', 'user_id' => null]);
        $room->members()->update(['status' => 'left', 'left_at' => now()]);

        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id' => $user->id,
            'type' => 'system',
            'message' => '🏁 Party room has been ended by the host. Thank you for joining!',
        ]);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Party room ended successfully.',
        ]);
    }

    /**
     * Search Users & Fetch Connected / Liked Friends to Invite.
     * GET /api/party-rooms/{id}/search-invitees
     * Query: query (search string)
     */
    public function searchInvitees(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        $occupiedUserIds = $room ? $room->seats()->whereNotNull('user_id')->pluck('user_id')->toArray() : [];

        $searchQuery = trim($request->input('query', ''));

        if (!empty($searchQuery)) {
            // Search by Name, Account ID, or Phone
            $users = User::where('id', '!=', $user->id)
                ->where('is_active', true)
                ->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                      ->orWhere('display_name', 'like', "%{$searchQuery}%")
                      ->orWhere('account_id', 'like', "%{$searchQuery}%")
                      ->orWhere('phone', 'like', "%{$searchQuery}%");
                })
                ->limit(30)
                ->get();
        } else {
            // Fetch connected / liked users (Users who I like + Users who like me + In-Room Audience)
            $likedUserIds = UserLike::where('sender_id', $user->id)->pluck('user_id')->toArray();
            $likedByMeUserIds = UserLike::where('user_id', $user->id)->pluck('sender_id')->toArray();
            $audienceUserIds = $room ? $room->members()->where('status', 'active')->where('user_id', '!=', $user->id)->pluck('user_id')->toArray() : [];

            $combinedIds = array_unique(array_merge($likedUserIds, $likedByMeUserIds, $audienceUserIds));

            if (empty($combinedIds)) {
                // Fallback to active popular users
                $users = User::where('id', '!=', $user->id)
                    ->where('is_active', true)
                    ->orderBy('level', 'desc')
                    ->limit(20)
                    ->get();
            } else {
                $users = User::whereIn('id', $combinedIds)
                    ->where('is_active', true)
                    ->limit(30)
                    ->get();
            }
        }

        $results = $users->map(function ($u) use ($occupiedUserIds, $user) {
            $isLikedByMe = UserLike::where('sender_id', $user->id)->where('user_id', $u->id)->exists();
            $isLikingMe = UserLike::where('sender_id', $u->id)->where('user_id', $user->id)->exists();

            return [
                'id' => $u->id,
                'account_id' => $u->account_id,
                'name' => $u->display_name ?? $u->name ?? 'User',
                'avatar_url' => $u->avatar_url,
                'avatar_frame_url' => $u->avatar_frame_url,
                'level' => (int) ($u->level ?? 1),
                'coins' => (int) ($u->coins ?? 0),
                'gender' => $u->gender ?? 'unspecified',
                'is_online' => (bool) $u->is_online,
                'is_on_seat' => in_array($u->id, $occupiedUserIds),
                'is_friend' => ($isLikedByMe && $isLikingMe),
                'is_liked' => $isLikedByMe,
            ];
        });

        return response()->json([
            'success' => true,
            'status' => true,
            'data' => $results,
        ]);
    }

    /**
     * Host Invites Guest to an Audio/Video Seat.
     * POST /api/party-rooms/{id}/invite-guest
     */
    public function inviteGuest(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room || $room->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Room is not active.'], 404);
        }

        if ($user->id !== $room->host_id && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the host can invite guests to seats.'], 403);
        }

        $targetUserId = $request->input('target_user_id') 
                     ?? $request->input('receiver_id') 
                     ?? $request->input('guest_id') 
                     ?? ($request->input('user_id') != $user->id ? $request->input('user_id') : null);
        $targetUser = $targetUserId ? (User::find($targetUserId) ?? User::where('account_id', $targetUserId)->first()) : null;

        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'Target user not found.'], 404);
        }

        // Find available seat
        $seatIndex = $request->input('seat_index');
        if ($seatIndex) {
            $seat = $room->seats()->where('seat_index', $seatIndex)->first();
            if (!$seat || $seat->status === 'occupied') {
                return response()->json(['success' => false, 'message' => "Seat {$seatIndex} is not available."], 422);
            }
        } else {
            $seat = $room->seats()->where('seat_index', '>', 1)->where('status', 'empty')->first();
            if (!$seat) {
                return response()->json(['success' => false, 'message' => 'All seats in this room are currently full.'], 422);
            }
            $seatIndex = $seat->seat_index;
        }

        // Create Invitation record
        $invitation = PartyRoomSeatInvitation::create([
            'party_room_id' => $room->id,
            'host_id' => $user->id,
            'user_id' => $targetUser->id,
            'seat_index' => $seatIndex,
            'status' => 'pending',
        ]);

        // Broadcast System Notice in chat
        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id' => $user->id,
            'type' => 'system',
            'message' => '💌 Host invited ' . ($targetUser->display_name ?? $targetUser->name) . " to join Seat #{$seatIndex}!",
        ]);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Invitation sent to guest successfully.',
            'data' => [
                'invitation_id' => $invitation->id,
                'seat_index' => $seatIndex,
                'target_user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->display_name ?? $targetUser->name,
                ],
            ],
        ]);
    }

    /**
     * Guest Responds to Seat Invitation (Accept / Decline).
     * POST /api/party-rooms/{id}/respond-invite
     */
    public function respondInvite(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room || $room->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Party room is not active.'], 404);
        }

        $invitationId = $request->input('invitation_id');
        $action = strtolower($request->input('action', 'accept')); // accept | decline

        $invitation = PartyRoomSeatInvitation::where('party_room_id', $room->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->when($invitationId, fn($q) => $q->where('id', $invitationId))
            ->latest()
            ->first();

        if (!$invitation) {
            return response()->json(['success' => false, 'message' => 'No pending invitation found.'], 404);
        }

        if ($action === 'decline' || $action === 'reject') {
            $invitation->update(['status' => 'rejected']);
            return response()->json(['success' => true, 'message' => 'Invitation declined.']);
        }

        // Verify seat is still empty
        $seat = $room->seats()->where('seat_index', $invitation->seat_index)->first();
        if (!$seat || $seat->status === 'occupied') {
            // Try to find another empty seat
            $seat = $room->seats()->where('seat_index', '>', 1)->where('status', 'empty')->first();
            if (!$seat) {
                $invitation->update(['status' => 'expired']);
                return response()->json(['success' => false, 'message' => 'Sorry, all guest seats are occupied.'], 422);
            }
        }

        // Assign user to seat
        $seat->update([
            'user_id' => $user->id,
            'role' => 'speaker',
            'status' => 'occupied',
            'joined_at' => now(),
            'last_billed_at' => now(),
        ]);

        $invitation->update(['status' => 'accepted']);

        // Update member role
        PartyRoomMember::updateOrCreate(
            ['party_room_id' => $room->id, 'user_id' => $user->id],
            ['role' => 'speaker', 'status' => 'active', 'last_active_at' => now()]
        );

        // System message
        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id' => $user->id,
            'type' => 'seat_join',
            'message' => '🎙️ ' . ($user->display_name ?? $user->name) . " joined Seat #{$seat->seat_index}!",
        ]);

        // Generate Publisher Streaming Token
        $rtcCredentials = $this->callingManager->initializeSession(
            $user,
            $room->channel_name,
            $room->room_type,
            'publisher'
        );

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => "You have taken Seat #{$seat->seat_index}!",
            'data' => [
                'seat_index' => $seat->seat_index,
                'room' => $this->formatRoomDetails($room, $user),
                'rtc' => $rtcCredentials,
            ],
        ]);
    }

    /**
     * Directly Take / Request an Open Seat.
     * POST /api/party-rooms/{id}/take-seat
     */
    public function takeSeat(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room || $room->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Party room is not active.'], 404);
        }

        // Check if user is already on a seat
        $existingSeat = $room->seats()->where('user_id', $user->id)->where('status', 'occupied')->first();
        if ($existingSeat) {
            return response()->json([
                'success' => true,
                'message' => "You are already on Seat #{$existingSeat->seat_index}.",
                'data' => ['seat_index' => $existingSeat->seat_index],
            ]);
        }

        $requestedIndex = $request->input('seat_index');
        if ($requestedIndex) {
            $seat = $room->seats()->where('seat_index', $requestedIndex)->first();
            if (!$seat || $seat->status === 'occupied' || $seat->is_locked) {
                return response()->json(['success' => false, 'message' => "Seat #{$requestedIndex} is not available."], 422);
            }
        } else {
            $seat = $room->seats()->where('seat_index', '>', 1)->where('status', 'empty')->where('is_locked', false)->first();
            if (!$seat) {
                return response()->json(['success' => false, 'message' => 'All seats in this room are full.'], 422);
            }
        }

        // Assign to seat
        $seat->update([
            'user_id' => $user->id,
            'role' => 'speaker',
            'status' => 'occupied',
            'joined_at' => now(),
            'last_billed_at' => now(),
        ]);

        PartyRoomMember::updateOrCreate(
            ['party_room_id' => $room->id, 'user_id' => $user->id],
            ['role' => 'speaker', 'status' => 'active', 'last_active_at' => now()]
        );

        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id' => $user->id,
            'type' => 'seat_join',
            'message' => '🎤 ' . ($user->display_name ?? $user->name) . " stepped up to Seat #{$seat->seat_index}!",
        ]);

        $rtcCredentials = $this->callingManager->initializeSession(
            $user,
            $room->channel_name,
            $room->room_type,
            'publisher'
        );

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => "You are now on Seat #{$seat->seat_index}!",
            'data' => [
                'seat_index' => $seat->seat_index,
                'room' => $this->formatRoomDetails($room, $user),
                'rtc' => $rtcCredentials,
            ],
        ]);
    }

    /**
     * Leave Seat Back to Audience.
     * POST /api/party-rooms/{id}/leave-seat
     */
    public function leaveSeat(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found.'], 404);
        }

        $seat = $room->seats()->where('user_id', $user->id)->where('seat_index', '>', 1)->first();
        if (!$seat) {
            return response()->json(['success' => false, 'message' => 'You are not occupying a guest seat.'], 422);
        }

        $seatIndex = $seat->seat_index;
        $seat->update([
            'user_id' => null,
            'status' => 'empty',
            'is_muted' => false,
            'is_video_muted' => false,
        ]);

        PartyRoomMember::where('party_room_id', $room->id)
            ->where('user_id', $user->id)
            ->update(['role' => 'audience']);

        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id' => $user->id,
            'type' => 'seat_leave',
            'message' => '🚶 ' . ($user->display_name ?? $user->name) . " stepped down from Seat #{$seatIndex}.",
        ]);

        $rtcCredentials = $this->callingManager->initializeSession(
            $user,
            $room->channel_name,
            $room->room_type,
            'audience'
        );

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Stepped down from seat successfully.',
            'data' => [
                'room' => $this->formatRoomDetails($room, $user),
                'rtc' => $rtcCredentials,
            ],
        ]);
    }

    /**
     * Host Kicks Guest from Seat.
     * POST /api/party-rooms/{id}/kick-seat
     */
    public function kickSeat(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found.'], 404);
        }

        if ($user->id !== $room->host_id && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the host can kick guests from seats.'], 403);
        }

        $seatIndex = $request->input('seat_index');
        $targetUserId = $request->input('user_id');

        $seatQuery = $room->seats()->where('seat_index', '>', 1);
        if ($seatIndex) {
            $seatQuery->where('seat_index', $seatIndex);
        } elseif ($targetUserId) {
            $seatQuery->where('user_id', $targetUserId);
        }

        $seat = $seatQuery->first();
        if (!$seat || !$seat->user_id) {
            return response()->json(['success' => false, 'message' => 'No active guest found on this seat.'], 422);
        }

        $kickedUser = $seat->user;
        $seat->update([
            'user_id' => null,
            'status' => 'empty',
            'is_muted' => false,
            'is_video_muted' => false,
        ]);

        if ($kickedUser) {
            PartyRoomMember::where('party_room_id', $room->id)
                ->where('user_id', $kickedUser->id)
                ->update(['role' => 'audience']);

            PartyRoomMessage::create([
                'party_room_id' => $room->id,
                'user_id' => $user->id,
                'type' => 'system',
                'message' => '⚠️ Host removed ' . ($kickedUser->display_name ?? $kickedUser->name) . " from Seat #{$seat->seat_index}.",
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => "Guest removed from Seat #{$seat->seat_index}.",
            'data' => [
                'room' => $this->formatRoomDetails($room, $user),
            ],
        ]);
    }

    /**
     * Toggle Mic Mute / Unmute on Seat.
     * POST /api/party-rooms/{id}/toggle-mic
     */
    public function toggleMic(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found.'], 404);
        }

        $seat = $room->seats()->where('user_id', $user->id)->first();
        if (!$seat) {
            return response()->json(['success' => false, 'message' => 'You are not occupying a seat.'], 422);
        }

        $isMuted = $request->has('is_muted') ? $request->boolean('is_muted') : !$seat->is_muted;
        $seat->update(['is_muted' => $isMuted]);

        return response()->json([
            'success' => true,
            'status' => true,
            'is_muted' => $isMuted,
            'message' => $isMuted ? 'Microphone muted.' : 'Microphone unmuted.',
        ]);
    }

    /**
     * Toggle Video Camera on Seat (For Video Party).
     * POST /api/party-rooms/{id}/toggle-video
     */
    public function toggleVideo(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found.'], 404);
        }

        $seat = $room->seats()->where('user_id', $user->id)->first();
        if (!$seat) {
            return response()->json(['success' => false, 'message' => 'You are not occupying a seat.'], 422);
        }

        $isVideoMuted = $request->has('is_video_muted') ? $request->boolean('is_video_muted') : !$seat->is_video_muted;
        $seat->update(['is_video_muted' => $isVideoMuted]);

        return response()->json([
            'success' => true,
            'status' => true,
            'is_video_muted' => $isVideoMuted,
            'message' => $isVideoMuted ? 'Camera turned off.' : 'Camera turned on.',
        ]);
    }

    /**
     * Send Group Chat Message or Photo/Image Attachment in Party Room.
     * Uploads images to `public/uploads/host_image/`.
     * POST /api/party-rooms/{id}/messages/send
     */
    public function sendMessage(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room || $room->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Party room is not active.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:1000',
            'type' => 'nullable|in:text,image',
            'image' => 'nullable',
            'file' => 'nullable|image|max:15360',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $type = $request->input('type', 'text');
        $imagePath = null;

        // Image file upload saved to public/uploads/host_image/
        if ($request->hasFile('image') || $request->hasFile('file') || $request->hasFile('photo')) {
            $file = $request->file('image') ?: $request->file('file') ?: $request->file('photo');
            if ($file && $file->isValid()) {
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = 'party_img_' . time() . '_' . Str::random(10) . '.' . $ext;
                $file->move(public_path('uploads/host_image'), $filename);
                $imagePath = 'uploads/host_image/' . $filename;
                $type = 'image';
            }
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->image_url;
            $type = 'image';
        }

        $messageText = trim($request->input('message', ''));
        if (empty($messageText) && empty($imagePath)) {
            return response()->json(['success' => false, 'message' => 'Message content or image is required.'], 422);
        }

        $msg = PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id' => $user->id,
            'type' => $type,
            'message' => $messageText,
            'image_url' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'Message sent successfully.',
            'data' => [
                'id' => $msg->id,
                'room_id' => $room->room_id,
                'type' => $msg->type,
                'message' => $msg->message,
                'image_url' => $msg->full_image_url,
                'created_at' => $msg->created_at?->toIso8601String(),
                'sender' => $msg->user_profile,
            ],
        ]);
    }

    /**
     * Get In-Room Chat Stream Messages.
     * GET /api/party-rooms/{id}/messages
     * Query: since_id, limit
     */
    public function getMessages(Request $request, $id): JsonResponse
    {
        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Party room not found.'], 404);
        }

        $limit = min(100, max(1, (int) $request->input('limit', 50)));
        $sinceId = $request->input('since_id');

        $query = PartyRoomMessage::with(['user', 'receiver', 'gift'])
            ->where('party_room_id', $room->id);

        if ($sinceId) {
            $query->where('id', '>', (int) $sinceId);
        }

        $messages = $query->orderBy('id', 'desc')->limit($limit)->get()->reverse()->values();

        $data = $messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'type' => $msg->type,
                'message' => $msg->message,
                'image_url' => $msg->full_image_url,
                'gift' => $msg->gift ? [
                    'id' => $msg->gift->id,
                    'name' => $msg->gift->name,
                    'icon_url' => $msg->gift->icon_url,
                    'animation_url' => $msg->gift->animation_url,
                    'coin_price' => $msg->gift->coin_price,
                    'count' => $msg->gift_count,
                ] : null,
                'coins_amount' => $msg->coins_amount,
                'sender' => $msg->user_profile,
                'receiver' => $msg->receiver_profile,
                'created_at' => $msg->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * Send Gift in Party Room (Check Wallet Balance -> Transfer Coins -> Animation).
     * POST /api/party-rooms/{id}/send-gift
     */
    public function sendGift(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room || $room->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Party room is not active.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'gift_id' => 'required|exists:gifts,id',
            'receiver_id' => 'nullable',
            'count' => 'nullable|integer|min:1|max:999',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $gift = Gift::findOrFail($request->gift_id);
        $count = (int) ($request->input('count') ?: 1);
        $totalCost = (int) ($gift->coin_price * $count);

        // Resolve Receiver (defaults to Host if not specified)
        $receiverId = $request->input('receiver_id');
        $receiver = $receiverId 
            ? (User::find($receiverId) ?? User::where('account_id', $receiverId)->first()) 
            : $room->host;

        if (!$receiver) {
            return response()->json(['success' => false, 'message' => 'Gift recipient not found.'], 404);
        }

        // Verify Sender Wallet Balance
        $senderCoins = (int) ($user->coins ?? 0);
        if ($senderCoins < $totalCost) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => "Insufficient coin balance. You need {$totalCost} coins but have {$senderCoins} coins.",
                'required_coins' => $totalCost,
                'current_coins' => $senderCoins,
            ], 402);
        }

        return DB::transaction(function () use ($user, $receiver, $gift, $count, $totalCost, $room) {
            // Deduct coins from sender
            $user->decrement('coins', $totalCost);

            // Credit recipient (e.g. 100% or standard charm increase)
            $receiver->increment('coins', $totalCost);

            // Update room total earnings
            $room->increment('total_earned_coins', $totalCost);

            // Create Coin Transaction Log
            CoinTransaction::create([
                'user_id' => $user->id,
                'amount' => -$totalCost,
                'balance_after' => (int) $user->fresh()->coins,
                'type' => 'party_room_gift_sent',
                'description' => "Sent {$count}x {$gift->name} to " . ($receiver->display_name ?? $receiver->name) . " in Party Room {$room->room_id}",
            ]);

            CoinTransaction::create([
                'user_id' => $receiver->id,
                'amount' => $totalCost,
                'balance_after' => (int) $receiver->fresh()->coins,
                'type' => 'party_room_gift_received',
                'description' => "Received {$count}x {$gift->name} from " . ($user->display_name ?? $user->name) . " in Party Room {$room->room_id}",
            ]);

            // Create Gift Transaction
            GiftTransaction::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'gift_id' => $gift->id,
                'gift_count' => $count,
                'total_coins' => $totalCost,
            ]);

            // Broadcast In-Room Gift Message
            $msg = PartyRoomMessage::create([
                'party_room_id' => $room->id,
                'user_id' => $user->id,
                'receiver_id' => $receiver->id,
                'type' => 'gift',
                'message' => "🎁 Sent {$count}x {$gift->name} to " . ($receiver->display_name ?? $receiver->name) . "!",
                'gift_id' => $gift->id,
                'gift_count' => $count,
                'coins_amount' => $totalCost,
                'extra_data' => [
                    'gift_name' => $gift->name,
                    'gift_icon' => $gift->icon_url,
                    'gift_animation' => $gift->animation_url,
                ],
            ]);

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => "Gift {$gift->name} sent successfully!",
                'data' => [
                    'remaining_coins' => (int) $user->fresh()->coins,
                    'total_cost' => $totalCost,
                    'gift' => [
                        'id' => $gift->id,
                        'name' => $gift->name,
                        'icon_url' => $gift->icon_url,
                        'animation_url' => $gift->animation_url,
                        'count' => $count,
                    ],
                    'receiver' => [
                        'id' => $receiver->id,
                        'name' => $receiver->display_name ?? $receiver->name,
                    ],
                ],
            ]);
        });
    }

    /**
     * Real-Time Minute Billing & 50/50 Revenue Split for Guest Seats.
     * (100 coins/min default -> 50% Host, 50% Admin).
     * If user balance is insufficient, auto-evicts user from seat back to audience.
     * POST /api/party-rooms/{id}/deduct-interval
     */
    public function deductInterval(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $room = PartyRoom::where('id', $id)->orWhere('room_id', $id)->first();
        if (!$room || $room->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Party room is not active.'], 404);
        }

        // Host is exempt from billing
        if ($user->id === $room->host_id) {
            return response()->json([
                'success' => true,
                'is_host' => true,
                'message' => 'Host is exempt from seat billing.',
            ]);
        }

        $seat = $room->seats()->where('user_id', $user->id)->where('status', 'occupied')->first();
        if (!$seat) {
            return response()->json(['success' => false, 'message' => 'User is not occupying a seat.'], 422);
        }

        $ratePerMinute = (int) ($room->coin_rate_per_minute ?: 100);
        $minutes = max(1, (int) $request->input('minutes', 1));
        $totalCost = $ratePerMinute * $minutes;

        // Check Guest Balance
        $userCoins = (int) ($user->coins ?? 0);
        if ($userCoins < $totalCost) {
            // AUTO EVICT FROM SEAT BACK TO AUDIENCE
            $seat->update([
                'user_id' => null,
                'status' => 'empty',
                'is_muted' => false,
                'is_video_muted' => false,
            ]);

            PartyRoomMember::where('party_room_id', $room->id)
                ->where('user_id', $user->id)
                ->update(['role' => 'audience']);

            PartyRoomMessage::create([
                'party_room_id' => $room->id,
                'user_id' => $user->id,
                'type' => 'system',
                'message' => '⚠️ ' . ($user->display_name ?? $user->name) . " left Seat #{$seat->seat_index} due to insufficient coin balance.",
            ]);

            return response()->json([
                'success' => false,
                'status' => false,
                'insufficient_balance' => true,
                'evicted_from_seat' => true,
                'message' => "Insufficient coins ({$userCoins}/{$totalCost}). You have been moved to audience.",
                'current_coins' => $userCoins,
                'required_coins' => $totalCost,
            ], 402);
        }

        // Split Calculation (50% Host, 50% Admin)
        $hostPercentage = (float) ($room->host_commission_percentage ?: 50.00) / 100.0;
        $adminPercentage = (float) ($room->admin_commission_percentage ?: 50.00) / 100.0;

        $hostCoins = (int) round($totalCost * $hostPercentage);
        $adminCoins = (int) ($totalCost - $hostCoins);

        return DB::transaction(function () use ($user, $room, $seat, $totalCost, $hostCoins, $adminCoins) {
            // Deduct from Guest
            $user->decrement('coins', $totalCost);

            // Credit Host Wallet
            $host = $room->host;
            if ($host) {
                $host->increment('coins', $hostCoins);

                CoinTransaction::create([
                    'user_id' => $host->id,
                    'amount' => $hostCoins,
                    'balance_after' => (int) $host->fresh()->coins,
                    'type' => 'party_room_seat_host_earning',
                    'description' => "Earned {$hostCoins} coins (50% split) from guest in Room {$room->room_id}",
                ]);
            }

            // Update Room Stats
            $room->increment('total_earned_coins', $hostCoins);
            $room->increment('total_admin_earned_coins', $adminCoins);

            // Update Seat Spent & Timestamp
            $seat->increment('coins_spent', $totalCost);
            $seat->update(['last_billed_at' => now()]);

            // Log Guest Deduction
            CoinTransaction::create([
                'user_id' => $user->id,
                'amount' => -$totalCost,
                'balance_after' => (int) $user->fresh()->coins,
                'type' => 'party_room_seat_billing',
                'description' => "Paid {$totalCost} coins for {$room->room_type} seat in Party Room {$room->room_id}",
            ]);

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => "Billed {$totalCost} coins successfully. Host received {$hostCoins} (50%), Admin received {$adminCoins} (50%).",
                'data' => [
                    'remaining_coins' => (int) $user->fresh()->coins,
                    'deducted_coins' => $totalCost,
                    'host_earned_coins' => $hostCoins,
                    'admin_earned_coins' => $adminCoins,
                    'seat_index' => $seat->seat_index,
                ],
            ]);
        });
    }

    /**
     * Format Complete Room Details Payload for Mobile App Screens.
     */
    protected function formatRoomDetails(PartyRoom $room, ?User $currentUser = null): array
    {
        $seats = $room->seats()->with('user')->orderBy('seat_index', 'asc')->get();

        $formattedSeats = $seats->map(function ($seat) {
            return [
                'seat_index' => (int) $seat->seat_index,
                'role' => $seat->role,
                'status' => $seat->status,
                'is_occupied' => ($seat->status === 'occupied' && !empty($seat->user_id)),
                'is_muted' => (bool) $seat->is_muted,
                'is_video_muted' => (bool) $seat->is_video_muted,
                'is_locked' => (bool) $seat->is_locked,
                'coins_spent' => (int) $seat->coins_spent,
                'user' => $seat->user_profile,
            ];
        });

        $currentUserSeat = null;
        if ($currentUser) {
            $mySeat = $seats->firstWhere('user_id', $currentUser->id);
            if ($mySeat) {
                $currentUserSeat = [
                    'seat_index' => (int) $mySeat->seat_index,
                    'role' => $mySeat->role,
                    'is_host' => ($mySeat->seat_index === 1 || $currentUser->id === $room->host_id),
                    'is_muted' => (bool) $mySeat->is_muted,
                    'is_video_muted' => (bool) $mySeat->is_video_muted,
                ];
            }
        }

        return [
            'id' => $room->id,
            'room_id' => $room->room_id,
            'room_title' => $room->room_title,
            'room_type' => $room->room_type,
            'topic_tag' => $room->topic_tag,
            'room_cover' => $room->room_cover_url,
            'background_image' => $room->background_image_url,
            'channel_name' => $room->channel_name,
            'max_seats' => (int) $room->max_seats,
            'occupied_seats_count' => (int) $room->occupied_seats_count,
            'online_members_count' => (int) $room->online_members_count,
            'coin_rate_per_minute' => (int) $room->coin_rate_per_minute,
            'host_commission_percentage' => (float) $room->host_commission_percentage,
            'admin_commission_percentage' => (float) $room->admin_commission_percentage,
            'total_earned_coins' => (int) $room->total_earned_coins,
            'status' => $room->status,
            'is_locked' => (bool) $room->is_locked,
            'announcement' => $room->announcement,
            'is_host' => $currentUser ? ($currentUser->id === $room->host_id) : false,
            'current_user_seat' => $currentUserSeat,
            'host' => [
                'id' => $room->host?->id,
                'account_id' => $room->host?->account_id,
                'name' => $room->host?->display_name ?? $room->host?->name ?? 'Host',
                'avatar_url' => $room->host?->avatar_url,
                'avatar_frame_url' => $room->host?->avatar_frame_url,
                'level' => (int) ($room->host?->level ?? 1),
                'coins' => (int) ($room->host?->coins ?? 0),
            ],
            'seats' => $formattedSeats,
            'created_at' => $room->created_at?->toIso8601String(),
        ];
    }
}
