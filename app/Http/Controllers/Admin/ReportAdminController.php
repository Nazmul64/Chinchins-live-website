<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use App\Models\User;
use Illuminate\Http\Request;

class ReportAdminController extends Controller
{
    /**
     * Display listing of in-chat & user reports.
     */
    public function index(Request $request)
    {
        $query = UserReport::with(['reporter', 'reportedUser'])->latest();

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($type = $request->input('reason_type')) {
            $query->where('reason_type', $type);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('reportedUser', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('account_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhereHas('reporter', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('account_id', 'like', "%{$search}%");
            });
        }

        $reports = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => UserReport::count(),
            'pending' => UserReport::where('status', 'pending')->count(),
            'resolved' => UserReport::where('status', 'resolved')->count(),
            'dismissed' => UserReport::where('status', 'dismissed')->count(),
        ];

        return view('admin.reports.index', compact('reports', 'stats'));
    }

    /**
     * Update report status (Resolve or Dismiss).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $report = UserReport::findOrFail($id);
        $report->status = $request->input('status');
        $report->admin_notes = $request->input('admin_notes');
        if ($report->status === 'resolved') {
            $report->resolved_at = now();
        }
        $report->save();

        return back()->with('success', "Report status updated to {$report->status}.");
    }

    /**
     * Quick Action: Block the reported user and mark report as resolved.
     */
    public function blockAndResolve(Request $request, $id)
    {
        $report = UserReport::with('reportedUser')->findOrFail($id);
        $user = $report->reportedUser;

        if ($user) {
            $user->is_locked = true;
            $user->is_active = false;
            $user->locked_reason = "Account suspended due to report #{$report->id}: {$report->reason_title}";
            $user->locked_at = now();
            $user->save();
        }

        $report->status = 'resolved';
        $report->admin_notes = 'User blocked and report resolved by Admin';
        $report->resolved_at = now();
        $report->save();

        return back()->with('success', "Reported user {$user?->display_name} has been BLOCKED and report resolved.");
    }
}
