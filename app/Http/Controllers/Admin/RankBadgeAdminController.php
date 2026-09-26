<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodRankBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class RankBadgeAdminController extends Controller
{
    /**
     * Display listing of Daily, Weekly, and Monthly Rank Badges.
     */
    public function index()
    {
        $dailyBadges = PeriodRankBadge::where('period_type', 'daily')
            ->orderBy('category')
            ->orderBy('rank_position')
            ->get();

        $weeklyBadges = PeriodRankBadge::where('period_type', 'weekly')
            ->orderBy('category')
            ->orderBy('rank_position')
            ->get();

        $monthlyBadges = PeriodRankBadge::where('period_type', 'monthly')
            ->orderBy('category')
            ->orderBy('rank_position')
            ->get();

        $stats = [
            'total'   => PeriodRankBadge::count(),
            'daily'   => $dailyBadges->count(),
            'weekly'  => $weeklyBadges->count(),
            'monthly' => $monthlyBadges->count(),
            'active'  => PeriodRankBadge::where('is_active', true)->count(),
        ];

        return view('admin.ranks.badges', compact('dailyBadges', 'weeklyBadges', 'monthlyBadges', 'stats'));
    }

    /**
     * Store a newly created Rank Badge & Avatar Frame.
     */
    public function store(Request $request)
    {
        $request->validate([
            'badge_name'         => 'required|string|max:100',
            'period_type'        => 'required|in:daily,weekly,monthly',
            'category'           => 'required|in:rich,charm',
            'rank_position'      => 'required|integer|min:1|max:100',
            'min_required_coins' => 'required|numeric|min:0',
            'badge_icon'         => 'required|image|mimes:png,webp,gif,jpeg|max:4096',
            'avatar_frame'       => 'nullable|image|mimes:png,webp,gif,jpeg|max:4096',
        ]);

        // Upload badge icon: public/uploads/ranks/badges/
        $badgeRelativePath = null;
        if ($request->hasFile('badge_icon')) {
            $file = $request->file('badge_icon');
            $badgeDir = public_path('uploads/ranks/badges');
            if (!File::exists($badgeDir)) {
                File::makeDirectory($badgeDir, 0777, true, true);
            }
            $badgeName = $request->period_type . '_' . $request->category . '_rank' . $request->rank_position . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($badgeDir, $badgeName);
            $badgeRelativePath = 'uploads/ranks/badges/' . $badgeName;
        }

        // Upload avatar frame: public/uploads/ranks/frames/
        $frameRelativePath = null;
        if ($request->hasFile('avatar_frame')) {
            $file = $request->file('avatar_frame');
            $frameDir = public_path('uploads/ranks/frames');
            if (!File::exists($frameDir)) {
                File::makeDirectory($frameDir, 0777, true, true);
            }
            $frameName = $request->period_type . '_' . $request->category . '_frame' . $request->rank_position . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($frameDir, $frameName);
            $frameRelativePath = 'uploads/ranks/frames/' . $frameName;
        }

        PeriodRankBadge::create([
            'badge_name'         => $request->badge_name,
            'period_type'        => $request->period_type,
            'category'           => $request->category,
            'rank_position'      => $request->rank_position,
            'min_required_coins' => (int) $request->min_required_coins,
            'badge_icon'         => $badgeRelativePath,
            'avatar_frame'       => $frameRelativePath,
            'is_active'          => true,
        ]);

        // Clear cache so app immediately loads new data into local memory
        Cache::forget('app_period_rank_badges');

        return back()->with('success', ucfirst($request->period_type) . ' ' . ucfirst($request->category) . ' rank badge created successfully!');
    }

    /**
     * Toggle badge active status.
     */
    public function toggleStatus($id)
    {
        $badge = PeriodRankBadge::findOrFail($id);
        $badge->is_active = !$badge->is_active;
        $badge->save();

        Cache::forget('app_period_rank_badges');

        return back()->with('success', "Badge '{$badge->badge_name}' status updated.");
    }

    /**
     * Delete rank badge and uploaded assets.
     */
    public function destroy($id)
    {
        $badge = PeriodRankBadge::findOrFail($id);

        if ($badge->badge_icon && File::exists(public_path($badge->badge_icon))) {
            @unlink(public_path($badge->badge_icon));
        }

        if ($badge->avatar_frame && File::exists(public_path($badge->avatar_frame))) {
            @unlink(public_path($badge->avatar_frame));
        }

        $badge->delete();

        Cache::forget('app_period_rank_badges');

        return back()->with('success', "Badge deleted successfully.");
    }
}
