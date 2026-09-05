<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpendLessCard;
use App\Models\UserSpendLessSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SpendLessCardAdminController extends Controller
{
    /**
     * Display listing of all Spend Less, Get More Gems (Monthly & Weekly Cards).
     */
    public function index()
    {
        SpendLessCard::seedDefaultCards();

        $cards = SpendLessCard::orderBy('sort_order', 'asc')->get();
        $totalCards = $cards->count();
        $activeCards = $cards->where('is_active', true)->count();
        $totalSubscriptions = UserSpendLessSubscription::count();
        $activeSubscriptions = UserSpendLessSubscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        return view('admin.spend-less-cards.index', compact(
            'cards',
            'totalCards',
            'activeCards',
            'totalSubscriptions',
            'activeSubscriptions'
        ));
    }

    /**
     * Parse structured Daily Schedule from Request.
     */
    protected function parseDailySchedule(Request $request): array
    {
        if ($request->has('schedule_day') && is_array($request->input('schedule_day'))) {
            $days = $request->input('schedule_day', []);
            $coins = $request->input('schedule_coins', []);
            $extras = $request->input('schedule_extra', []);

            $schedule = [];
            foreach ($days as $idx => $dayNum) {
                $dayInt = (int) $dayNum;
                $coinInt = isset($coins[$idx]) ? (int) $coins[$idx] : 0;
                $extraText = isset($extras[$idx]) && trim($extras[$idx]) !== '' ? trim($extras[$idx]) : null;

                if ($dayInt > 0) {
                    $schedule[] = [
                        'day'   => $dayInt,
                        'coins' => $coinInt,
                        'extra' => $extraText,
                    ];
                }
            }

            usort($schedule, fn($a, $b) => $a['day'] <=> $b['day']);
            return $schedule;
        }

        if ($request->filled('daily_schedule_raw')) {
            $decoded = json_decode($request->input('daily_schedule_raw'), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Parse structured Extra Outfits/Rewards Perks from Request, including image file uploads.
     */
    protected function parseExtraRewards(Request $request, ?array $existingRewards = []): array
    {
        if ($request->has('perk_title') && is_array($request->input('perk_title'))) {
            $titles = $request->input('perk_title', []);
            $tags = $request->input('perk_tag', []);
            $icons = $request->input('perk_icon', []);
            $validities = $request->input('perk_validity', []);
            $imageUrls = $request->input('perk_image_url', []);
            $existingImages = $request->input('perk_existing_image', []);

            $destinationPath = public_path('uploads/spend_less_cards');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $rewards = [];
            foreach ($titles as $idx => $title) {
                $titleStr = trim($title);
                if ($titleStr === '') continue;

                $tagStr = isset($tags[$idx]) ? trim($tags[$idx]) : '';
                $validityStr = isset($validities[$idx]) ? trim($validities[$idx]) : ($tagStr ?: '7days');
                $iconStr = isset($icons[$idx]) ? trim($icons[$idx]) : 'frame_avatar';
                $imageUrlStr = isset($imageUrls[$idx]) ? trim($imageUrls[$idx]) : null;
                $currentImage = isset($existingImages[$idx]) ? trim($existingImages[$idx]) : null;

                $uploadedFilePath = null;
                if ($request->hasFile("perk_file_{$idx}")) {
                    $file = $request->file("perk_file_{$idx}");
                    $filename = 'perk_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $filename);
                    $uploadedFilePath = 'uploads/spend_less_cards/' . $filename;
                }

                $finalImage = $uploadedFilePath ?: ($imageUrlStr ?: $currentImage);

                $rewards[] = [
                    'title'    => $titleStr,
                    'tag'      => $tagStr ?: $validityStr,
                    'validity' => $validityStr,
                    'icon'     => $iconStr,
                    'image'    => $finalImage,
                ];
            }

            return $rewards;
        }

        if ($request->filled('extra_rewards_raw')) {
            $decoded = json_decode($request->input('extra_rewards_raw'), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $existingRewards ?? [];
    }

    /**
     * Store a newly created Card Package in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'card_type'            => 'required|string|max:100|unique:spend_less_cards,card_type',
            'price_bdt'            => 'required|numeric|min:0',
            'original_price_bdt'   => 'nullable|numeric|min:0',
            'price_coins'          => 'nullable|integer|min:0',
            'duration_days'        => 'required|integer|min:1|max:365',
            'instant_reward_coins' => 'required|integer|min:0',
            'badge_text'           => 'nullable|string|max:50',
            'card_color'           => 'nullable|string|max:30',
        ]);

        $destinationPath = public_path('uploads/spend_less_cards');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $iconUrl = null;
        if ($request->hasFile('icon_file')) {
            $file = $request->file('icon_file');
            $filename = 'icon_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $iconUrl = 'uploads/spend_less_cards/' . $filename;
        }

        $animationUrl = null;
        if ($request->hasFile('animation_file')) {
            $file = $request->file('animation_file');
            $filename = 'anim_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $animationUrl = 'uploads/spend_less_cards/' . $filename;
        }

        $schedule = $this->parseDailySchedule($request);
        $extraRewards = $this->parseExtraRewards($request);

        $instant = (int) $request->input('instant_reward_coins', 0);
        $dailyTotal = (int) $request->input('daily_checkin_total_coins', 0);
        $totalReturn = $request->filled('total_return_coins') ? (int) $request->input('total_return_coins') : ($instant + $dailyTotal);

        SpendLessCard::create([
            'card_type'                 => strtolower(trim($request->input('card_type'))),
            'name'                      => trim($request->input('name')),
            'category_name'             => $request->input('category_name') ?? $request->input('name'),
            'badge_text'                => $request->input('badge_text'),
            'price_bdt'                 => (float) $request->input('price_bdt'),
            'original_price_bdt'        => $request->filled('original_price_bdt') ? (float) $request->input('original_price_bdt') : null,
            'price_coins'               => (int) ($request->input('price_coins') ?? $instant),
            'duration_days'             => (int) $request->input('duration_days', 7),
            'instant_reward_coins'      => $instant,
            'instant_reward_text'       => $request->input('instant_reward_text'),
            'daily_checkin_total_coins' => $dailyTotal,
            'daily_checkin_text'        => $request->input('daily_checkin_text'),
            'total_return_coins'        => $totalReturn,
            'daily_schedule'            => $schedule,
            'extra_rewards'             => $extraRewards,
            'description'               => $request->input('description'),
            'card_color'                => $request->input('card_color') ?? '#EC4899',
            'banner_tag'                => $request->input('banner_tag'),
            'icon_url'                  => $iconUrl,
            'animation_url'             => $animationUrl,
            'format'                    => $request->input('format', 'image'),
            'is_active'                 => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            'sort_order'                => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.spend-less-cards.index')->with('success', 'New card package created successfully!');
    }

    /**
     * Update existing card package.
     */
    public function update(Request $request, $id)
    {
        $card = SpendLessCard::findOrFail($id);

        $request->validate([
            'name'                 => 'required|string|max:255',
            'card_type'            => 'required|string|max:100|unique:spend_less_cards,card_type,' . $card->id,
            'price_bdt'            => 'required|numeric|min:0',
            'original_price_bdt'   => 'nullable|numeric|min:0',
            'price_coins'          => 'nullable|integer|min:0',
            'duration_days'        => 'required|integer|min:1|max:365',
            'instant_reward_coins' => 'required|integer|min:0',
            'badge_text'           => 'nullable|string|max:50',
            'card_color'           => 'nullable|string|max:30',
        ]);

        $destinationPath = public_path('uploads/spend_less_cards');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $iconUrl = $card->icon_url;
        if ($request->hasFile('icon_file')) {
            $file = $request->file('icon_file');
            $filename = 'icon_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $iconUrl = 'uploads/spend_less_cards/' . $filename;
        }

        $animationUrl = $card->animation_url;
        if ($request->hasFile('animation_file')) {
            $file = $request->file('animation_file');
            $filename = 'anim_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $animationUrl = 'uploads/spend_less_cards/' . $filename;
        }

        $schedule = $this->parseDailySchedule($request);
        $extraRewards = $this->parseExtraRewards($request, $card->extra_rewards);

        $instant = (int) $request->input('instant_reward_coins', 0);
        $dailyTotal = (int) $request->input('daily_checkin_total_coins', 0);
        $totalReturn = $request->filled('total_return_coins') ? (int) $request->input('total_return_coins') : ($instant + $dailyTotal);

        $card->update([
            'card_type'                 => strtolower(trim($request->input('card_type'))),
            'name'                      => trim($request->input('name')),
            'category_name'             => $request->input('category_name') ?? $request->input('name'),
            'badge_text'                => $request->input('badge_text'),
            'price_bdt'                 => (float) $request->input('price_bdt'),
            'original_price_bdt'        => $request->filled('original_price_bdt') ? (float) $request->input('original_price_bdt') : null,
            'price_coins'               => (int) ($request->input('price_coins') ?? $instant),
            'duration_days'             => (int) $request->input('duration_days', 7),
            'instant_reward_coins'      => $instant,
            'instant_reward_text'       => $request->input('instant_reward_text'),
            'daily_checkin_total_coins' => $dailyTotal,
            'daily_checkin_text'        => $request->input('daily_checkin_text'),
            'total_return_coins'        => $totalReturn,
            'daily_schedule'            => $schedule,
            'extra_rewards'             => $extraRewards,
            'description'               => $request->input('description'),
            'card_color'                => $request->input('card_color') ?? '#EC4899',
            'banner_tag'                => $request->input('banner_tag'),
            'icon_url'                  => $iconUrl,
            'animation_url'             => $animationUrl,
            'format'                    => $request->input('format', $card->format),
            'is_active'                 => $request->has('is_active') ? (bool) $request->input('is_active') : false,
            'sort_order'                => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.spend-less-cards.index')->with('success', 'Card package updated successfully!');
    }

    /**
     * Delete a card package.
     */
    public function destroy($id)
    {
        $card = SpendLessCard::findOrFail($id);
        $card->delete();

        return redirect()->route('admin.spend-less-cards.index')->with('success', 'Card package deleted successfully!');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $card = SpendLessCard::findOrFail($id);
        $card->is_active = !$card->is_active;
        $card->save();

        $statusText = $card->is_active ? 'enabled' : 'disabled';
        return redirect()->route('admin.spend-less-cards.index')->with('success', "Card package {$statusText} successfully!");
    }

    /**
     * View User Subscriptions to Spend Less Cards.
     */
    public function subscriptions(Request $request)
    {
        $query = UserSpendLessSubscription::with(['user', 'card'])->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('card_type')) {
            $query->where('card_type', $request->input('card_type'));
        }

        $subscriptions = $query->paginate(20);
        return view('admin.spend-less-cards.subscriptions', compact('subscriptions'));
    }
}
