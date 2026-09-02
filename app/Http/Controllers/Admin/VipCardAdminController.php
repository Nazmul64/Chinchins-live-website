<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserVipCardSubscription;
use App\Models\VipPrivilegeCard;
use Illuminate\Http\Request;

class VipCardAdminController extends Controller
{
    /**
     * Display listing of all VIP / Monthly Card Packages.
     */
    public function index()
    {
        // Seed default 4 cards if empty
        VipPrivilegeCard::seedDefaultCards();

        $cards = VipPrivilegeCard::orderBy('sort_order', 'asc')->get();
        $totalCards = $cards->count();
        $activeCards = $cards->where('is_active', true)->count();
        $totalSubscriptions = UserVipCardSubscription::count();
        $activeSubscriptions = UserVipCardSubscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        return view('admin.vip-cards.index', compact(
            'cards',
            'totalCards',
            'activeCards',
            'totalSubscriptions',
            'activeSubscriptions'
        ));
    }

    /**
     * Store a newly created VIP Card package.
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_type'                 => 'required|string|max:50',
            'name'                      => 'required|string|max:100',
            'badge_text'                => 'nullable|string|max:50',
            'price_bdt'                 => 'required|numeric|min:0',
            'price_coins'               => 'required|integer|min:0',
            'duration_days'             => 'required|integer|min:1',
            'instant_reward_coins'      => 'required|integer|min:0',
            'daily_checkin_total_coins' => 'required|integer|min:0',
            'total_return_coins'        => 'required|integer|min:0',
            'card_color'                => 'nullable|string|max:20',
            'banner_tag'                => 'nullable|string|max:255',
            'description'               => 'nullable|string',
            'sort_order'                => 'nullable|integer',
        ]);

        $dailySchedule = [];
        if ($request->filled('daily_schedule_raw')) {
            $decoded = json_decode($request->input('daily_schedule_raw'), true);
            if (is_array($decoded)) {
                $dailySchedule = $decoded;
            }
        }

        $extraRewards = [];
        if ($request->filled('extra_rewards_raw')) {
            $decoded = json_decode($request->input('extra_rewards_raw'), true);
            if (is_array($decoded)) {
                $extraRewards = $decoded;
            }
        }

        VipPrivilegeCard::create([
            'card_type'                 => $request->input('card_type'),
            'name'                      => $request->input('name'),
            'badge_text'                => $request->input('badge_text', 'HOT'),
            'price_bdt'                 => $request->input('price_bdt'),
            'price_coins'               => $request->input('price_coins'),
            'duration_days'             => $request->input('duration_days', 7),
            'instant_reward_coins'      => $request->input('instant_reward_coins'),
            'daily_checkin_total_coins' => $request->input('daily_checkin_total_coins'),
            'total_return_coins'        => $request->input('total_return_coins'),
            'card_color'                => $request->input('card_color', '#FF4081'),
            'banner_tag'                => $request->input('banner_tag', 'Spend Less, Get More Gems!'),
            'description'               => $request->input('description'),
            'daily_schedule'            => $dailySchedule,
            'extra_rewards'             => $extraRewards,
            'is_active'                 => $request->boolean('is_active', true),
            'sort_order'                => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.vip-cards.index')
            ->with('success', 'VIP Privilege Card created successfully!');
    }

    /**
     * Update an existing VIP Card package.
     */
    public function update(Request $request, int $id)
    {
        $card = VipPrivilegeCard::findOrFail($id);

        $request->validate([
            'card_type'                 => 'required|string|max:50',
            'name'                      => 'required|string|max:100',
            'badge_text'                => 'nullable|string|max:50',
            'price_bdt'                 => 'required|numeric|min:0',
            'price_coins'               => 'required|integer|min:0',
            'duration_days'             => 'required|integer|min:1',
            'instant_reward_coins'      => 'required|integer|min:0',
            'daily_checkin_total_coins' => 'required|integer|min:0',
            'total_return_coins'        => 'required|integer|min:0',
            'card_color'                => 'nullable|string|max:20',
            'banner_tag'                => 'nullable|string|max:255',
            'description'               => 'nullable|string',
            'sort_order'                => 'nullable|integer',
        ]);

        $dailySchedule = $card->daily_schedule;
        if ($request->filled('daily_schedule_raw')) {
            $decoded = json_decode($request->input('daily_schedule_raw'), true);
            if (is_array($decoded)) {
                $dailySchedule = $decoded;
            }
        }

        $extraRewards = $card->extra_rewards;
        if ($request->filled('extra_rewards_raw')) {
            $decoded = json_decode($request->input('extra_rewards_raw'), true);
            if (is_array($decoded)) {
                $extraRewards = $decoded;
            }
        }

        $card->update([
            'card_type'                 => $request->input('card_type'),
            'name'                      => $request->input('name'),
            'badge_text'                => $request->input('badge_text'),
            'price_bdt'                 => $request->input('price_bdt'),
            'price_coins'               => $request->input('price_coins'),
            'duration_days'             => $request->input('duration_days'),
            'instant_reward_coins'      => $request->input('instant_reward_coins'),
            'daily_checkin_total_coins' => $request->input('daily_checkin_total_coins'),
            'total_return_coins'        => $request->input('total_return_coins'),
            'card_color'                => $request->input('card_color'),
            'banner_tag'                => $request->input('banner_tag'),
            'description'               => $request->input('description'),
            'daily_schedule'            => $dailySchedule,
            'extra_rewards'             => $extraRewards,
            'is_active'                 => $request->boolean('is_active'),
            'sort_order'                => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.vip-cards.index')
            ->with('success', "{$card->name} updated successfully!");
    }

    /**
     * Toggle active status of a card.
     */
    public function toggleStatus(int $id)
    {
        $card = VipPrivilegeCard::findOrFail($id);
        $card->is_active = !$card->is_active;
        $card->save();

        return redirect()->route('admin.vip-cards.index')
            ->with('success', "Status of {$card->name} changed to " . ($card->is_active ? 'Active' : 'Inactive'));
    }

    /**
     * Delete a VIP Card package.
     */
    public function destroy(int $id)
    {
        $card = VipPrivilegeCard::findOrFail($id);
        $name = $card->name;
        $card->delete();

        return redirect()->route('admin.vip-cards.index')
            ->with('success', "{$name} deleted successfully!");
    }

    /**
     * Display listing of User VIP Card Subscriptions.
     */
    public function subscriptions()
    {
        $subscriptions = UserVipCardSubscription::with(['user', 'card'])
            ->latest('id')
            ->paginate(20);

        return view('admin.vip-cards.subscriptions', compact('subscriptions'));
    }
}
