<?php

namespace App\Http\Controllers\Api;

use App\Events\CallAccepted;
use App\Events\CallCancelled;
use App\Events\CallEnded;
use App\Events\CallIncoming;
use App\Events\CallRejected;
use App\Events\WebRTCAnswer;
use App\Events\WebRTCICECandidate;
use App\Events\WebRTCOffer;
use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebRTCCallController extends Controller
{
    /**
     * Resolve authenticated user instance with token & header resilience.
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Check Authorization Bearer token
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

        // 2. Try Sanctum guard
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

        // 3. Check custom user ID headers
        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('user-id') 
                     ?? $request->header('userId')
                     ?? $request->header('X-Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        // 4. Check request body / query parameters
        $idParam = $request->input('user_id') 
                ?? $request->input('userId') 
                ?? $request->input('caller_id') 
                ?? $request->input('sender_id') 
                ?? $request->input('id');

        if ($idParam) {
            $u = User::find($idParam);
            if ($u) return $u;
        }

        return null;
    }

    /**
     * Find call by ID or Room ID.
     */
    protected function findCall(mixed $idOrRoom): ?Call
    {
        if (is_numeric($idOrRoom)) {
            return Call::with(['caller', 'receiver'])->find($idOrRoom);
        }
        return Call::with(['caller', 'receiver'])->where('room_id', $idOrRoom)->first();
    }

    /**
     * 1. Create Call (Caller initiates call)
     * POST /api/calls
     */
    public function store(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'call_type'   => 'nullable|in:audio,video',
            'room_id'     => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $receiverId = (int) $request->input('receiver_id');
        if ($user->id === $receiverId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot call yourself'
            ], 400);
        }

        $callType = $request->input('call_type', 'video');
        $roomId = $request->input('room_id') ?: ('call_' . Str::uuid()->toString());

        // Create call record
        $call = Call::create([
            'caller_id'   => $user->id,
            'receiver_id' => $receiverId,
            'call_type'   => $callType,
            'status'      => 'calling',
            'room_id'     => $roomId,
            'started_at'  => now(),
        ]);

        // Load caller details for event payload
        $call->load(['caller', 'receiver']);

        // Broadcast call.incoming event to receiver's private channel (private-user.{receiverId})
        try {
            event(new CallIncoming($call));
        } catch (\Throwable $e) {
            // Log or continue gracefully
        }

        return response()->json([
            'success' => true,
            'message' => 'Call initiated successfully',
            'call'    => [
                'id'          => $call->id,
                'caller_id'   => $call->caller_id,
                'receiver_id' => $call->receiver_id,
                'call_type'   => $call->call_type,
                'status'      => $call->status,
                'room_id'     => $call->room_id,
                'started_at'  => $call->started_at?->toIso8601String(),
            ]
        ], 201);
    }

    /**
     * 2. Accept Call (Receiver accepts call)
     * POST /api/calls/{call}/accept
     */
    public function accept(Request $request, mixed $call): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $callInstance = $this->findCall($call);
        if (!$callInstance) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }

        if ((int)$callInstance->receiver_id !== (int)$user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to accept this call'], 403);
        }

        $callInstance->update([
            'status'      => 'accepted',
            'answered_at' => now(),
        ]);

        $callInstance->load(['caller', 'receiver']);

        // Broadcast call.accepted to caller's channel (private-user.{caller_id})
        try {
            event(new CallAccepted($callInstance));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'call_id' => $callInstance->id,
            'room_id' => $callInstance->room_id,
            'status'  => 'accepted',
        ]);
    }

    /**
     * 3. Reject Call (Receiver declines call)
     * POST /api/calls/{call}/reject
     */
    public function reject(Request $request, mixed $call): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $callInstance = $this->findCall($call);
        if (!$callInstance) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }

        $reason = $request->input('reason', 'declined');

        $callInstance->update([
            'status'   => 'rejected',
            'ended_at' => now(),
            'ended_by' => $user->id,
        ]);

        // Broadcast call.rejected to caller's channel (private-user.{caller_id})
        try {
            event(new CallRejected($callInstance, $reason));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'call_id' => $callInstance->id,
            'status'  => 'rejected',
        ]);
    }

    /**
     * 4. Cancel Call (Caller cancels call before receiver picks up)
     * POST /api/calls/{call}/cancel
     */
    public function cancel(Request $request, mixed $call): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $callInstance = $this->findCall($call);
        if (!$callInstance) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }

        $callInstance->update([
            'status'   => 'cancelled',
            'ended_at' => now(),
            'ended_by' => $user->id,
        ]);

        // Broadcast call.cancelled to receiver's channel (private-user.{receiver_id})
        try {
            event(new CallCancelled($callInstance));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'call_id' => $callInstance->id,
            'status'  => 'cancelled',
        ]);
    }

    /**
     * 5. End Call (Either participant hangs up)
     * POST /api/calls/{call}/end
     */
    public function end(Request $request, mixed $call): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $callInstance = $this->findCall($call);
        if (!$callInstance) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }

        $targetUserId = ($callInstance->caller_id === $user->id) ? $callInstance->receiver_id : $callInstance->caller_id;

        $callInstance->update([
            'status'   => 'ended',
            'ended_at' => now(),
            'ended_by' => $user->id,
        ]);

        // Broadcast call.ended to the other user
        try {
            event(new CallEnded($callInstance, $user->id, $targetUserId));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'call_id' => $callInstance->id,
            'status'  => 'ended',
        ]);
    }

    /**
     * 6. WebRTC SDP Offer Relay
     * POST /api/calls/{call}/offer
     */
    public function offer(Request $request, mixed $call): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $callInstance = $this->findCall($call);
        if (!$callInstance) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }

        $sdp = $request->input('sdp');
        if (empty($sdp)) {
            return response()->json(['success' => false, 'message' => 'SDP offer is required'], 422);
        }

        $targetUserId = ($callInstance->caller_id === $user->id) ? $callInstance->receiver_id : $callInstance->caller_id;

        // Extract extra fields without modifying sdp
        $extraPayload = $request->except(['sdp', 'call_id', 'room_id', 'type']);

        // Broadcast unmodified SDP offer to receiver
        try {
            event(new WebRTCOffer(
                targetUserId: $targetUserId,
                callId: $callInstance->id,
                roomId: $callInstance->room_id,
                sdp: $sdp,
                extraPayload: $extraPayload
            ));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Offer relayed successfully',
            'call_id' => $callInstance->id,
        ]);
    }

    /**
     * 7. WebRTC SDP Answer Relay
     * POST /api/calls/{call}/answer
     */
    public function answer(Request $request, mixed $call): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $callInstance = $this->findCall($call);
        if (!$callInstance) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }

        $sdp = $request->input('sdp');
        if (empty($sdp)) {
            return response()->json(['success' => false, 'message' => 'SDP answer is required'], 422);
        }

        $targetUserId = ($callInstance->caller_id === $user->id) ? $callInstance->receiver_id : $callInstance->caller_id;
        $extraPayload = $request->except(['sdp', 'call_id', 'room_id', 'type']);

        // Broadcast unmodified SDP answer to caller
        try {
            event(new WebRTCAnswer(
                targetUserId: $targetUserId,
                callId: $callInstance->id,
                roomId: $callInstance->room_id,
                sdp: $sdp,
                extraPayload: $extraPayload
            ));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Answer relayed successfully',
            'call_id' => $callInstance->id,
        ]);
    }

    /**
     * 8. WebRTC ICE Candidate Relay
     * POST /api/calls/{call}/ice-candidate
     */
    public function iceCandidate(Request $request, mixed $call): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $callInstance = $this->findCall($call);
        if (!$callInstance) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }

        $candidate = $request->input('candidate');
        $sdpMid = $request->input('sdpMid') ?? $request->input('sdp_mid');
        $sdpMLineIndex = $request->input('sdpMLineIndex') ?? $request->input('sdp_m_line_index');

        $targetUserId = ($callInstance->caller_id === $user->id) ? $callInstance->receiver_id : $callInstance->caller_id;
        $extraPayload = $request->except(['candidate', 'sdpMid', 'sdp_mid', 'sdpMLineIndex', 'sdp_m_line_index', 'call_id', 'room_id']);

        // Broadcast unmodified ICE Candidate to the peer
        try {
            event(new WebRTCICECandidate(
                targetUserId: $targetUserId,
                callId: $callInstance->id,
                roomId: $callInstance->room_id,
                candidate: $candidate,
                sdpMid: $sdpMid,
                sdpMLineIndex: $sdpMLineIndex !== null ? (int)$sdpMLineIndex : null,
                extraPayload: $extraPayload
            ));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'ICE candidate relayed successfully',
        ]);
    }

    /**
     * 9. Unified WebRTC Signal Relay Endpoint
     * POST /api/calls/{call}/signal
     */
    public function signal(Request $request, mixed $call): JsonResponse
    {
        $type = strtolower($request->input('type', ''));

        if ($type === 'offer') {
            return $this->offer($request, $call);
        }

        if ($type === 'answer') {
            return $this->answer($request, $call);
        }

        if ($type === 'candidate' || $type === 'ice_candidate' || $type === 'ice-candidate' || $request->has('candidate')) {
            return $this->iceCandidate($request, $call);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid signal type. Expected: offer, answer, or candidate'
        ], 422);
    }

    /**
     * 10. Call History
     * GET /api/calls
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $calls = Call::with([
            'caller:id,name,display_name,avatar,avatar_url',
            'receiver:id,name,display_name,avatar,avatar_url',
        ])
        ->forUser($user->id)
        ->orderByDesc('id')
        ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $calls,
        ]);
    }

    /**
     * 11. Call Details
     * GET /api/calls/{call}
     */
    public function show(Request $request, mixed $call): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $callInstance = $this->findCall($call);
        if (!$callInstance) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }

        $callInstance->load(['caller', 'receiver', 'endedBy']);

        return response()->json([
            'success' => true,
            'call'    => $callInstance,
        ]);
    }
}
