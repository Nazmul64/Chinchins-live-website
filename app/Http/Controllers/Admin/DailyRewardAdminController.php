<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyReward;
use App\Models\UserDailyClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DailyRewardAdminController extends Controller
{
    /**
     * Ensure default 7 daily rewards exist.
     */
    protected function ensureDefaultRewards()
    {
        if (DailyReward::count() === 0) {
            for ($i = 1; $i <= 7; $i++) {
                DailyReward::create([
                    'day_number'   => $i,
                    'reward_coins' => ($i === 7 ? 100 : 50),
                    'is_active'    => true,
                    'icon_image'   => null,
                ]);
            }
        }
    }

    /**
     * Display Daily Check-in Rewards Management.
     */
    public function index(Request $request)
    {
        $this->ensureDefaultRewards();
        $rewards = DailyReward::orderBy('day_number')->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['status' => true, 'data' => $rewards]);
        }

        $totalClaims = UserDailyClaim::count();
        $todayClaims = UserDailyClaim::whereDate('claimed_at', today())->count();
        $totalCoinsDistributed = UserDailyClaim::sum('coins_awarded');
        $recentClaims = UserDailyClaim::with('user')->latest('claimed_at')->limit(15)->get();

        return view('admin.daily-rewards.index', compact(
            'rewards',
            'totalClaims',
            'todayClaims',
            'totalCoinsDistributed',
            'recentClaims'
        ));
    }

    /**
     * Update Reward Day config (Coins, image upload, active status).
     */
    public function update(Request $request, $id)
    {
        $reward = DailyReward::findOrFail($id);

        $request->validate([
            'reward_coins' => 'nullable|integer|min:1',
            'is_active'    => 'nullable|boolean',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $path = public_path('uploads/claim');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            // Remove old uploaded image if exists
            if ($reward->icon_image && File::exists(public_path($reward->icon_image))) {
                try {
                    File::delete(public_path($reward->icon_image));
                } catch (\Throwable $e) {}
            }

            $image = $request->file('image');
            $imageName = 'day_' . $reward->day_number . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move($path, $imageName);

            $reward->icon_image = 'uploads/claim/' . $imageName;
        }

        if ($request->filled('reward_coins')) {
            $reward->reward_coins = (int) $request->reward_coins;
        }

        if ($request->has('is_active')) {
            $reward->is_active = $request->boolean('is_active');
        }

        $reward->save();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status'  => true,
                'message' => "Day {$reward->day_number} updated successfully!",
                'data'    => $reward,
            ]);
        }

        return redirect()->route('admin.daily-rewards.index')->with('success', "Day {$reward->day_number} reward configuration updated successfully!");
    }

    /**
     * Toggle Active status of a day.
     */
    public function toggleStatus($id)
    {
        $reward = DailyReward::findOrFail($id);
        $reward->is_active = !$reward->is_active;
        $reward->save();

        return redirect()->route('admin.daily-rewards.index')->with('success', "Day {$reward->day_number} status updated.");
    }
}
