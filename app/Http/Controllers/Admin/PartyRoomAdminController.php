<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartyRoom;
use App\Models\PartyRoomMessage;
use App\Models\PartyRoomSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartyRoomAdminController extends Controller
{
    /**
     * Display Party Rooms List & Live Dashboard.
     */
    public function index(Request $request)
    {
        $query = PartyRoom::with(['host', 'activeSeats.user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
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

        $rooms = $query->paginate(15)->withQueryString();

        // High Level Analytics Metrics
        $totalActiveRooms = PartyRoom::where('status', 'active')->count();
        $totalVoiceRooms = PartyRoom::where('status', 'active')->where('room_type', 'voice')->count();
        $totalVideoRooms = PartyRoom::where('status', 'active')->where('room_type', 'video')->count();
        $totalHostCoins = PartyRoom::sum('total_earned_coins');
        $totalAdminCoins = PartyRoom::sum('total_admin_earned_coins');

        return view('admin.party_rooms.index', compact(
            'rooms',
            'totalActiveRooms',
            'totalVoiceRooms',
            'totalVideoRooms',
            'totalHostCoins',
            'totalAdminCoins'
        ));
    }

    /**
     * Inspect Live Room (Seats, Chat messages, Participants, Speaker Queue).
     */
    public function show($id)
    {
        $room = PartyRoom::with(['host', 'seats.user', 'messages.user', 'messages.gift'])->findOrFail($id);
        $recentMessages = $room->messages()->with(['user', 'receiver', 'gift'])->latest()->limit(80)->get()->reverse();
        $pendingRequests = $room->invitations()->with('user')->where('status', 'pending')->latest()->get();
        $activeAudience = $room->members()->with('user')->where('status', 'active')->where('role', 'audience')->limit(30)->get();

        return view('admin.party_rooms.show', compact('room', 'recentMessages', 'pendingRequests', 'activeAudience'));
    }

    /**
     * Admin Kicks a Seated Guest.
     */
    public function kickSeat($id, $seatIndex)
    {
        $room = PartyRoom::findOrFail($id);
        $seat = $room->seats()->where('seat_index', $seatIndex)->where('status', 'occupied')->first();

        if (!$seat || !$seat->user_id) {
            return redirect()->back()->with('error', "No active occupant found on Seat #{$seatIndex}.");
        }

        $kickedUser = $seat->user;
        $seat->update([
            'user_id' => null,
            'status'  => 'empty',
            'is_muted' => false,
            'is_video_muted' => false,
        ]);

        if ($kickedUser) {
            \App\Models\PartyRoomMember::where('party_room_id', $room->id)
                ->where('user_id', $kickedUser->id)
                ->update(['role' => 'audience']);

            PartyRoomMessage::create([
                'party_room_id' => $room->id,
                'user_id'       => auth()->id() ?? $room->host_id,
                'type'          => 'system',
                'message'       => '⚠️ Admin removed ' . ($kickedUser->display_name ?? $kickedUser->name) . " from Seat #{$seatIndex}.",
            ]);
        }

        try {
            broadcast(new \App\Events\SeatUpdatedEvent($room->id, [
                'room_id'     => (string) $room->id,
                'room_name'   => $room->channel_name ?: $room->room_id,
                'seat_index'  => (int) $seatIndex,
                'user_id'     => null,
                'is_muted'    => 0,
                'is_occupied' => false,
                'action'      => 'kick_seat',
                'can_publish' => false,
                'user'        => null,
                'timestamp'   => now()->toIso8601String(),
            ]))->toOthers();
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', "Seat #{$seatIndex} occupant removed successfully.");
    }

    /**
     * Admin Mutes / Unmutes a Seated Guest.
     */
    public function toggleMuteSeat($id, $seatIndex)
    {
        $room = PartyRoom::findOrFail($id);
        $seat = $room->seats()->where('seat_index', $seatIndex)->where('status', 'occupied')->first();

        if (!$seat || !$seat->user_id) {
            return redirect()->back()->with('error', "No speaker found on Seat #{$seatIndex}.");
        }

        $newMuted = !$seat->is_muted;
        $seat->update(['is_muted' => $newMuted]);

        try {
            broadcast(new \App\Events\SeatUpdatedEvent($room->id, [
                'room_id'     => (string) $room->id,
                'room_name'   => $room->channel_name ?: $room->room_id,
                'seat_index'  => (int) $seatIndex,
                'user_id'     => $seat->user_id,
                'is_muted'    => $newMuted ? 1 : 0,
                'is_occupied' => true,
                'action'      => 'host_mute_seat',
                'timestamp'   => now()->toIso8601String(),
            ]))->toOthers();
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', "Seat #{$seatIndex} microphone " . ($newMuted ? 'muted.' : 'unmuted.'));
    }

    /**
     * Admin Responds to Pending Seat Request (Accept / Reject).
     */
    public function respondRequest(Request $request, $id, $requestId)
    {
        $room = PartyRoom::findOrFail($id);
        $seatReq = \App\Models\PartyRoomSeatInvitation::where('party_room_id', $room->id)
            ->where('id', $requestId)
            ->firstOrFail();

        $action = $request->input('action', 'accept'); // accept | reject
        $targetUser = $seatReq->user;

        if ($action === 'reject' || $action === 'cancel') {
            $seatReq->update(['status' => 'rejected']);
            return redirect()->back()->with('success', "Speaker request for {$targetUser?->name} has been rejected (বাতিল করা হয়েছে).");
        }

        // Accept
        $emptySeat = $room->seats()->where('seat_index', '>', 1)->where('status', 'empty')->first();
        if (!$emptySeat) {
            return redirect()->back()->with('error', 'All guest seats are currently full.');
        }

        $emptySeat->update([
            'user_id'        => $seatReq->user_id,
            'role'           => 'speaker',
            'status'         => 'occupied',
            'is_muted'       => false,
            'is_video_muted' => false,
            'joined_at'      => now(),
            'last_billed_at' => now(),
        ]);

        $seatReq->update(['status' => 'accepted', 'seat_index' => $emptySeat->seat_index]);

        \App\Models\PartyRoomMember::updateOrCreate(
            ['party_room_id' => $room->id, 'user_id' => $seatReq->user_id],
            ['role' => 'speaker', 'status' => 'active', 'last_active_at' => now()]
        );

        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id'       => auth()->id() ?? $room->host_id,
            'type'          => 'seat_join',
            'message'       => '🎉 Admin accepted ' . ($targetUser?->display_name ?? $targetUser?->name) . " to Speaker Stage (Seat #{$emptySeat->seat_index})!",
        ]);

        try {
            broadcast(new \App\Events\SeatUpdatedEvent($room->id, [
                'room_id'     => (string) $room->id,
                'room_name'   => $room->channel_name ?: $room->room_id,
                'seat_index'  => (int) $emptySeat->seat_index,
                'user_id'     => $targetUser?->id,
                'is_muted'    => 0,
                'is_occupied' => true,
                'action'      => 'take_seat',
                'can_publish' => true,
                'user'        => [
                    'id'               => $targetUser?->id,
                    'account_id'       => $targetUser?->account_id,
                    'name'             => $targetUser?->display_name ?? $targetUser?->name,
                    'display_name'     => $targetUser?->display_name ?? $targetUser?->name,
                    'avatar_url'       => $targetUser?->avatar_url,
                    'level'            => (int) ($targetUser?->level ?? 1),
                ],
                'timestamp'   => now()->toIso8601String(),
            ]))->toOthers();
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', "Accepted {$targetUser?->name} to Seat #{$emptySeat->seat_index} (গ্রহণ করা হয়েছে)!");
    }

    /**
     * Admin Sends Announcement Message into Room Chat Stream.
     */
    public function sendAdminMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $room = PartyRoom::findOrFail($id);

        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id'       => auth()->id() ?? $room->host_id,
            'type'          => 'system',
            'message'       => '🛡️ [Admin Notice]: ' . trim($request->message),
        ]);

        return redirect()->back()->with('success', 'Admin announcement sent to room chat.');
    }

    /**
     * Force End / Close Inappropriate Party Room.
     */
    public function forceClose($id)
    {
        $room = PartyRoom::findOrFail($id);
        $room->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        $room->seats()->update(['status' => 'empty', 'user_id' => null]);
        $room->members()->update(['status' => 'left', 'left_at' => now()]);

        PartyRoomMessage::create([
            'party_room_id' => $room->id,
            'user_id' => auth()->id() ?? $room->host_id,
            'type' => 'system',
            'message' => '🛑 This party room has been closed by System Admin.',
        ]);

        return redirect()->back()->with('success', "Party room #{$room->room_id} has been ended successfully.");
    }

    /**
     * Show Party Room Global Settings (Rates, Split %, Tags).
     */
    public function settings()
    {
        $settings = PartyRoomSetting::getSettings();
        return view('admin.party_rooms.settings', compact('settings'));
    }

    /**
     * Update Party Room Global Settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'default_voice_rate_per_minute' => 'required|integer|min:0',
            'default_video_rate_per_minute' => 'required|integer|min:0',
            'host_commission_percentage' => 'required|numeric|min:0|max:100',
            'admin_commission_percentage' => 'required|numeric|min:0|max:100',
            'max_guests_per_room' => 'required|integer|min:4|max:16',
            'is_party_room_enabled' => 'nullable|boolean',
            'default_announcement' => 'nullable|string|max:500',
        ]);

        $settings = PartyRoomSetting::getSettings();
        $settings->update([
            'default_voice_rate_per_minute' => $request->default_voice_rate_per_minute,
            'default_video_rate_per_minute' => $request->default_video_rate_per_minute,
            'host_commission_percentage' => $request->host_commission_percentage,
            'admin_commission_percentage' => $request->admin_commission_percentage,
            'max_guests_per_room' => $request->max_guests_per_room,
            'is_party_room_enabled' => $request->has('is_party_room_enabled'),
            'default_announcement' => $request->default_announcement,
        ]);

        return redirect()->back()->with('success', 'Party Room configuration updated successfully.');
    }
}
