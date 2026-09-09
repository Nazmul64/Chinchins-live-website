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
     * Inspect Live Room (Seats, Chat messages, Participants).
     */
    public function show($id)
    {
        $room = PartyRoom::with(['host', 'seats.user', 'messages.user', 'messages.gift'])->findOrFail($id);
        $recentMessages = $room->messages()->with(['user', 'receiver', 'gift'])->latest()->limit(50)->get()->reverse();

        return view('admin.party_rooms.show', compact('room', 'recentMessages'));
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
            'max_guests_per_room' => 'required|integer|min:4|max:12',
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
