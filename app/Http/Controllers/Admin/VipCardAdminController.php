<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\UserVipCardSubscription;
use App\Models\VipPrivilegeCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VipCardAdminController extends Controller
{
    /**
     * Display listing of all VIP / Monthly Card Packages and Floating Banner settings.
     */
    public function index()
    {
        // Seed default cards if empty or update missing fields
        VipPrivilegeCard::seedDefaultCards();

        $cards = VipPrivilegeCard::orderBy('sort_order', 'asc')->get();
        $totalCards = $cards->count();
        $activeCards = $cards->where('is_active', true)->count();
        $totalSubscriptions = UserVipCardSubscription::count();
        $activeSubscriptions = UserVipCardSubscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        $floatingBannerConfig = AppSetting::getAppConfig()['floating_vip_banner'] ?? [
            'is_enabled'    => true,
            'title'         => 'Extra Gems',
            'tag'           => 'Monthly Card',
            'image_url'     => asset('assets/images/vip/floating_extra_gems.png'),
            'action_type'   => 'OPEN_PREMIUM_VIP',
            'target_screen' => '/premium-vip',
        ];

        return view('admin.vip-cards.index', compact(
            'cards',
            'totalCards',
            'activeCards',
            'totalSubscriptions',
            'activeSubscriptions',
            'floatingBannerConfig'
        ));
    }

    /**
     * Parse structured Daily Schedule from Request.
     */
    protected function parseDailySchedule(Request $request): array
    {
        // 1. Structured array from dynamic schedule builder
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

            // Sort by day ascending
            usort($schedule, fn($a, $b) => $a['day'] <=> $b['day']);
            return $schedule;
        }

        // 2. Fallback JSON raw string
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
        // 1. Structured array from dynamic perks builder
        if ($request->has('perk_title') && is_array($request->input('perk_title'))) {
            $titles = $request->input('perk_title', []);
            $tags = $request->input('perk_tag', []);
            $icons = $request->input('perk_icon', []);
            $validities = $request->input('perk_validity', []);
            $imageUrls = $request->input('perk_image_url', []);
            $existingImages = $request->input('perk_existing_image', []);

            $destinationPath = public_path('uploads/vip_cards');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $rewards = [];
            foreach ($titles as $idx => $title) {
                $titleStr = trim($title);
                if ($titleStr === '') continue;

                $tagStr = isset($tags[$idx]) ? trim($tags[$idx]) : '';
                $validityStr = isset($validities[$idx]) ? trim($validities[$idx]) : ($tagStr ?: '30days');
                $iconStr = isset($icons[$idx]) ? trim($icons[$idx]) : 'frame_avatar';
                $imageUrlStr = isset($imageUrls[$idx]) ? trim($imageUrls[$idx]) : null;
                $currentImage = isset($existingImages[$idx]) ? trim($existingImages[$idx]) : null;

                // Check if a new file was uploaded for this perk row
                $uploadedFilePath = null;
                if ($request->hasFile("perk_file_{$idx}")) {
                    $file = $request->file("perk_file_{$idx}");
                    $filename = 'perk_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $filename);
                    $uploadedFilePath = 'uploads/vip_cards/' . $filename;
                } elseif ($request->hasFile("perk_files.{$idx}")) {
                    $file = $request->file("perk_files.{$idx}");
                    $filename = 'perk_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $filename);
                    $uploadedFilePath = 'uploads/vip_cards/' . $filename;
                }

                // Final image path priority: New Uploaded File > Custom Image URL > Existing Saved Image > null
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

        // 2. Fallback JSON raw string
        if ($request->filled('extra_rewards_raw')) {
            $decoded = json_decode($request->input('extra_rewards_raw'), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $existingRewards ?? [];
    }

    /**
     * Store a newly created VIP Card package.
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_type'                 => 'required|string|max:50',
            'name'                      => 'required|string|max:100',
            'category_name'             => 'nullable|string|max:100',
            'badge_text'                => 'nullable|string|max:50',
            'price_bdt'                 => 'required|numeric|min:0',
            'original_price_bdt'        => 'nullable|numeric|min:0',
            'price_coins'               => 'required|integer|min:0',
            'duration_days'             => 'required|integer|min:1',
            'instant_reward_coins'      => 'required|integer|min:0',
            'instant_reward_text'       => 'nullable|string|max:100',
            'daily_checkin_total_coins' => 'nullable|integer|min:0',
            'daily_checkin_text'        => 'nullable|string|max:100',
            'total_return_coins'        => 'nullable|integer|min:0',
            'card_color'                => 'nullable|string|max:20',
            'banner_tag'                => 'nullable|string|max:255',
            'icon'                      => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'icon_url'                  => 'nullable|string|max:255',
            'animation_file'            => 'nullable|file|max:25600',
            'animation_url'             => 'nullable|string|max:255',
            'bg_image'                  => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'bg_image_url'              => 'nullable|string|max:255',
            'format'                    => 'nullable|string|in:lottie,svga,webp,gif,image,mp4',
            'description'               => 'nullable|string',
            'sort_order'                => 'nullable|integer',
        ]);

        $destinationPath = public_path('uploads/vip_cards');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $iconPath = $request->input('icon_url');
        if ($request->hasFile('icon')) {
            $iconFile = $request->file('icon');
            $filename = 'vip_icon_' . time() . '_' . Str::random(6) . '.' . $iconFile->getClientOriginalExtension();
            $iconFile->move($destinationPath, $filename);
            $iconPath = 'uploads/vip_cards/' . $filename;
        }

        $animationPath = $request->input('animation_url');
        $format = $request->input('format', 'lottie');
        if ($request->hasFile('animation_file')) {
            $animFile = $request->file('animation_file');
            $ext = strtolower($animFile->getClientOriginalExtension());
            $filename = 'vip_anim_' . time() . '_' . Str::random(6) . '.' . $ext;
            $animFile->move($destinationPath, $filename);
            $animationPath = 'uploads/vip_cards/' . $filename;
            if ($ext === 'svga') {
                $format = 'svga';
            } elseif (in_array($ext, ['json', 'lottie'])) {
                $format = 'lottie';
            } elseif ($ext === 'webp') {
                $format = 'webp';
            } elseif ($ext === 'gif') {
                $format = 'gif';
            }
        }

        $bgImagePath = $request->input('bg_image_url');
        if ($request->hasFile('bg_image')) {
            $bgFile = $request->file('bg_image');
            $filename = 'vip_bg_' . time() . '_' . Str::random(6) . '.' . $bgFile->getClientOriginalExtension();
            $bgFile->move($destinationPath, $filename);
            $bgImagePath = 'uploads/vip_cards/' . $filename;
        }

        $dailySchedule = $this->parseDailySchedule($request);
        $extraRewards = $this->parseExtraRewards($request);

        // Auto-calculate daily check-in total coins if schedule exists
        $calculatedDailyTotal = 0;
        foreach ($dailySchedule as $item) {
            $calculatedDailyTotal += (int) ($item['coins'] ?? 0);
        }

        $instantCoins = (int) $request->input('instant_reward_coins');
        $dailyBonusCoins = $request->filled('daily_checkin_total_coins') && (int) $request->input('daily_checkin_total_coins') > 0
            ? (int) $request->input('daily_checkin_total_coins')
            : $calculatedDailyTotal;

        $totalReturnCoins = $request->filled('total_return_coins') && (int) $request->input('total_return_coins') > 0
            ? (int) $request->input('total_return_coins')
            : ($instantCoins + $dailyBonusCoins);

        VipPrivilegeCard::create([
            'card_type'                 => $request->input('card_type'),
            'name'                      => $request->input('name'),
            'category_name'             => $request->input('category_name') ?: $request->input('name'),
            'badge_text'                => $request->input('badge_text', 'HOT'),
            'price_bdt'                 => $request->input('price_bdt'),
            'original_price_bdt'        => $request->input('original_price_bdt'),
            'price_coins'               => $request->input('price_coins'),
            'duration_days'             => $request->input('duration_days', 7),
            'instant_reward_coins'      => $instantCoins,
            'instant_reward_text'       => $request->input('instant_reward_text') ?: ('Gems in total ' . number_format($instantCoins)),
            'daily_checkin_total_coins' => $dailyBonusCoins,
            'daily_checkin_text'        => $request->input('daily_checkin_text') ?: ('Gems in total ' . number_format($dailyBonusCoins)),
            'total_return_coins'        => $totalReturnCoins,
            'card_color'                => $request->input('card_color', '#FF4081'),
            'banner_tag'                => $request->input('banner_tag', 'Spend Less, Get More Gems!'),
            'icon_url'                  => $iconPath,
            'animation_url'             => $animationPath,
            'bg_image_url'              => $bgImagePath,
            'format'                    => $format,
            'description'               => $request->input('description'),
            'daily_schedule'            => $dailySchedule,
            'extra_rewards'             => $extraRewards,
            'is_active'                 => $request->boolean('is_active', true),
            'sort_order'                => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.vip-cards.index')
            ->with('success', "VIP Privilege Card \"{$request->input('name')}\" created successfully!");
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
            'category_name'             => 'nullable|string|max:100',
            'badge_text'                => 'nullable|string|max:50',
            'price_bdt'                 => 'required|numeric|min:0',
            'original_price_bdt'        => 'nullable|numeric|min:0',
            'price_coins'               => 'required|integer|min:0',
            'duration_days'             => 'required|integer|min:1',
            'instant_reward_coins'      => 'required|integer|min:0',
            'instant_reward_text'       => 'nullable|string|max:100',
            'daily_checkin_total_coins' => 'nullable|integer|min:0',
            'daily_checkin_text'        => 'nullable|string|max:100',
            'total_return_coins'        => 'nullable|integer|min:0',
            'card_color'                => 'nullable|string|max:20',
            'banner_tag'                => 'nullable|string|max:255',
            'icon'                      => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'icon_url'                  => 'nullable|string|max:255',
            'animation_file'            => 'nullable|file|max:25600',
            'animation_url'             => 'nullable|string|max:255',
            'bg_image'                  => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'bg_image_url'              => 'nullable|string|max:255',
            'format'                    => 'nullable|string|in:lottie,svga,webp,gif,image,mp4',
            'description'               => 'nullable|string',
            'sort_order'                => 'nullable|integer',
        ]);

        $destinationPath = public_path('uploads/vip_cards');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        if ($request->hasFile('icon')) {
            $iconFile = $request->file('icon');
            $filename = 'vip_icon_' . time() . '_' . Str::random(6) . '.' . $iconFile->getClientOriginalExtension();
            $iconFile->move($destinationPath, $filename);
            $card->icon_url = 'uploads/vip_cards/' . $filename;
        } elseif ($request->filled('icon_url')) {
            $card->icon_url = $request->input('icon_url');
        }

        if ($request->hasFile('animation_file')) {
            $animFile = $request->file('animation_file');
            $ext = strtolower($animFile->getClientOriginalExtension());
            $filename = 'vip_anim_' . time() . '_' . Str::random(6) . '.' . $ext;
            $animFile->move($destinationPath, $filename);
            $card->animation_url = 'uploads/vip_cards/' . $filename;
            if ($ext === 'svga') {
                $card->format = 'svga';
            } elseif (in_array($ext, ['json', 'lottie'])) {
                $card->format = 'lottie';
            } elseif ($ext === 'webp') {
                $card->format = 'webp';
            } elseif ($ext === 'gif') {
                $card->format = 'gif';
            }
        } elseif ($request->filled('animation_url')) {
            $card->animation_url = $request->input('animation_url');
        }

        if ($request->hasFile('bg_image')) {
            $bgFile = $request->file('bg_image');
            $filename = 'vip_bg_' . time() . '_' . Str::random(6) . '.' . $bgFile->getClientOriginalExtension();
            $bgFile->move($destinationPath, $filename);
            $card->bg_image_url = 'uploads/vip_cards/' . $filename;
        } elseif ($request->filled('bg_image_url')) {
            $card->bg_image_url = $request->input('bg_image_url');
        }

        if ($request->filled('format')) {
            $card->format = $request->input('format');
        }

        $dailySchedule = $this->parseDailySchedule($request);
        if (empty($dailySchedule) && !$request->has('schedule_day') && !$request->filled('daily_schedule_raw')) {
            $dailySchedule = $card->daily_schedule;
        }

        $extraRewards = $this->parseExtraRewards($request, $card->extra_rewards);
        if (empty($extraRewards) && !$request->has('perk_title') && !$request->filled('extra_rewards_raw')) {
            $extraRewards = $card->extra_rewards;
        }

        // Auto-calculate daily check-in total coins if schedule exists
        $calculatedDailyTotal = 0;
        foreach ($dailySchedule as $item) {
            $calculatedDailyTotal += (int) ($item['coins'] ?? 0);
        }

        $instantCoins = (int) $request->input('instant_reward_coins');
        $dailyBonusCoins = $request->filled('daily_checkin_total_coins') && (int) $request->input('daily_checkin_total_coins') > 0
            ? (int) $request->input('daily_checkin_total_coins')
            : ($calculatedDailyTotal > 0 ? $calculatedDailyTotal : $card->daily_checkin_total_coins);

        $totalReturnCoins = $request->filled('total_return_coins') && (int) $request->input('total_return_coins') > 0
            ? (int) $request->input('total_return_coins')
            : ($instantCoins + $dailyBonusCoins);

        $card->update([
            'card_type'                 => $request->input('card_type'),
            'name'                      => $request->input('name'),
            'category_name'             => $request->input('category_name') ?: $request->input('name'),
            'badge_text'                => $request->input('badge_text'),
            'price_bdt'                 => $request->input('price_bdt'),
            'original_price_bdt'        => $request->input('original_price_bdt'),
            'price_coins'               => $request->input('price_coins'),
            'duration_days'             => $request->input('duration_days'),
            'instant_reward_coins'      => $instantCoins,
            'instant_reward_text'       => $request->input('instant_reward_text') ?: ('Gems in total ' . number_format($instantCoins)),
            'daily_checkin_total_coins' => $dailyBonusCoins,
            'daily_checkin_text'        => $request->input('daily_checkin_text') ?: ('Gems in total ' . number_format($dailyBonusCoins)),
            'total_return_coins'        => $totalReturnCoins,
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
     * Update Floating Home Screen Banner Settings.
     */
    public function updateFloatingBanner(Request $request)
    {
        $request->validate([
            'floating_vip_banner_title'  => 'nullable|string|max:100',
            'floating_vip_banner_tag'    => 'nullable|string|max:100',
            'floating_vip_banner_action' => 'nullable|string|max:100',
            'floating_banner_file'       => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        AppSetting::set(
            'floating_vip_banner_enabled',
            $request->boolean('floating_vip_banner_enabled') ? '1' : '0',
            'vip',
            'Show floating Extra Gems VIP banner on Home Screen'
        );

        if ($request->filled('floating_vip_banner_title')) {
            AppSetting::set('floating_vip_banner_title', $request->input('floating_vip_banner_title'), 'vip');
        }

        if ($request->filled('floating_vip_banner_tag')) {
            AppSetting::set('floating_vip_banner_tag', $request->input('floating_vip_banner_tag'), 'vip');
        }

        if ($request->filled('floating_vip_banner_action')) {
            AppSetting::set('floating_vip_banner_action', $request->input('floating_vip_banner_action'), 'vip');
        }

        if ($request->hasFile('floating_banner_file')) {
            $dest = public_path('uploads/vip_cards');
            if (!File::exists($dest)) {
                File::makeDirectory($dest, 0777, true, true);
            }
            $file = $request->file('floating_banner_file');
            $filename = 'floating_banner_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dest, $filename);
            AppSetting::set('floating_vip_banner_image', 'uploads/vip_cards/' . $filename, 'vip');
        }

        return redirect()->route('admin.vip-cards.index')
            ->with('success', 'Home screen floating VIP banner updated successfully!');
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
