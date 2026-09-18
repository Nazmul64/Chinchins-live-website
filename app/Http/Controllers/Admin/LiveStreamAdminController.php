<?php

namespace App\Http\Controllers\Admin;

use App\Events\LiveStreamEnded;
use App\Http\Controllers\Controller;
use App\Models\GiftTransaction;
use App\Models\LiveRoom;
use App\Models\LiveStream;
use Illuminate\Http\Request;

class LiveStreamAdminController extends Controller
{
    /**
     * Display all live stream broadcasts and active rooms.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = LiveStream::with(['host', 'guests.user', 'viewers.user'])->latest('id');

        if ($status === 'live') {
            $query->where('status', 'live');
        } elseif ($status === 'ended') {
            $query->where('status', 'ended');
        }

        $streams = $query->paginate(20)->withQueryString();

        $activeLivesCount = 0;
        $totalDiamondsEarned = 0;
        $totalTransactionsCount = 0;

        try { $activeLivesCount = LiveStream::where('status', 'live')->count(); } catch (\Throwable $e) {}
        try { $totalDiamondsEarned = (int) LiveStream::sum('total_diamonds_earned'); } catch (\Throwable $e) {}
        try { $totalTransactionsCount = GiftTransaction::count(); } catch (\Throwable $e) {}

        return view('admin.live_streams.index', compact(
            'streams',
            'status',
            'activeLivesCount',
            'totalDiamondsEarned',
            'totalTransactionsCount'
        ));
    }

    /**
     * Terminate / Force Close an inappropriate or active live stream.
     */
    public function forceClose(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);

        $stream->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        if ($stream->host) {
            $stream->host->update([
                'online_status'  => 'available',
                'current_status' => 'available',
                'is_busy'        => false,
            ]);
        }

        $summary = [
            'live_stream_id'        => $stream->id,
            'channel_name'          => $stream->channel_name,
            'duration_seconds'      => $stream->ended_at->diffInSeconds($stream->started_at),
            'total_diamonds_earned' => (int) $stream->total_diamonds_earned,
            'peak_viewers'          => (int) $stream->viewer_count,
            'terminated_by_admin'   => true,
        ];

        try {
            event(new LiveStreamEnded($stream->id, $summary));
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', "Live Stream #{$stream->id} has been forcefully terminated.");
    }

    /**
     * Virtual Gift Transactions Audit Log.
     */
    public function giftTransactions(Request $request)
    {
        $transactions = GiftTransaction::with(['sender', 'receiver', 'gift', 'liveStream'])
            ->latest('id')
            ->paginate(30);

        $totalSpent = GiftTransaction::sum('coins_spent');
        $totalTxCount = GiftTransaction::count();

        return view('admin.live_streams.gift_transactions', compact('transactions', 'totalSpent', 'totalTxCount'));
    }
}
