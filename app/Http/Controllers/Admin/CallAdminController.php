<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\CallSetting;
use Illuminate\Http\Request;

class CallAdminController extends Controller
{
    /**
     * Display listing of all call sessions with metrics and filters.
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 'all');
        $status = $request->input('status', 'all');

        $query = CallSession::with(['caller', 'receiver'])->latest();

        if (in_array($type, ['video', 'audio'])) {
            $query->where('call_type', $type);
        }

        if ($type === 'free_trial') {
            $query->where('is_free_trial', true);
        }

        if (in_array($status, ['connected', 'ended', 'initiated', 'failed'])) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('channel_name', 'like', "%{$search}%")
                  ->orWhereHas('caller', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('display_name', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('receiver', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('display_name', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  });
            });
        }

        $calls = $query->paginate(15)->withQueryString();

        $totalSeconds = CallSession::sum('duration_seconds');
        $totalMinutes = round($totalSeconds / 60, 1);

        $stats = [
            'total_calls' => CallSession::count(),
            'connected_calls' => CallSession::where('status', 'connected')->count(),
            'total_minutes' => $totalMinutes,
            'total_coins_spent' => CallSession::sum('coins_deducted'),
            'total_host_earnings' => CallSession::sum('host_earned_coins'),
            'total_admin_revenue' => CallSession::sum('admin_revenue_coins'),
            'free_trials_count' => CallSession::where('is_free_trial', true)->count(),
        ];

        $config = CallSetting::getAllConfig();

        return view('admin.calls.index', compact('calls', 'stats', 'type', 'status', 'config'));
    }

    /**
     * Display Call Settings and Revenue Sharing configuration form.
     */
    public function settings()
    {
        $config = CallSetting::getAllConfig();

        return view('admin.calls.settings', compact('config'));
    }

    /**
     * Update Call Settings and Revenue Sharing percentages.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'is_call_enabled' => 'nullable|boolean',
            'is_free_call_enabled' => 'nullable|boolean',
            'free_call_duration_seconds' => 'required|integer|min:1|max:300',
            'free_calls_per_user' => 'required|integer|min:0|max:10',
            'video_call_rate_per_minute' => 'required|integer|min:1',
            'audio_call_rate_per_minute' => 'required|integer|min:1',
            'host_earning_percent' => 'required|numeric|min:0|max:100',
        ]);

        $hostPercent = (float) $request->input('host_earning_percent');
        $adminPercent = max(0, 100 - $hostPercent);

        CallSetting::set('is_call_enabled', $request->boolean('is_call_enabled') ? '1' : '0', 'Global toggle for Audio and Video calling');
        CallSetting::set('is_free_call_enabled', $request->boolean('is_free_call_enabled') ? '1' : '0', 'Enable or disable free trial calling for new registrations');
        CallSetting::set('free_call_duration_seconds', $request->input('free_call_duration_seconds'), 'Free trial duration in seconds (e.g. 10s, 30s)');
        CallSetting::set('free_calls_per_user', $request->input('free_calls_per_user'), 'Number of free trial calls per new user');
        CallSetting::set('video_call_rate_per_minute', $request->input('video_call_rate_per_minute'), 'Default video call rate in coins per minute');
        CallSetting::set('audio_call_rate_per_minute', $request->input('audio_call_rate_per_minute'), 'Default audio call rate in coins per minute');
        CallSetting::set('host_earning_percent', $hostPercent, 'Percentage of call coins credited to Host (female user)');
        CallSetting::set('admin_commission_percent', $adminPercent, 'Percentage of call coins kept as Admin Platform Revenue');

        return back()->with('success', "Call rates & revenue split updated! Host receives {$hostPercent}%, Platform receives {$adminPercent}%.");
    }
}
