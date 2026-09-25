<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfileIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CustomerProfileIconAdminController extends Controller
{
    /**
     * Display listing of all Customer Profile Icons for the "Me" Screen.
     */
    public function index()
    {
        $icons = CustomerProfileIcon::orderBy('sort_order', 'asc')->get();
        $totalIcons = $icons->count();
        $customUploadedCount = $icons->whereNotNull('icon_path')->count();
        $defaultIconsCount = $totalIcons - $customUploadedCount;
        $activeIconsCount = $icons->where('is_active', true)->count();

        return view('admin.customer-profile-icons.index', compact(
            'icons',
            'totalIcons',
            'customUploadedCount',
            'defaultIconsCount',
            'activeIconsCount'
        ));
    }

    /**
     * Store a new Customer Profile Icon with uploaded image.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:100',
            'key'          => 'nullable|string|max:100',
            'category'     => 'required|string|in:wallet_card,banner_card,action_menu',
            'subtitle'     => 'nullable|string|max:255',
            'badge_text'   => 'nullable|string|max:50',
            'badge_color'  => 'nullable|string|max:50',
            'target_route' => 'nullable|string|max:100',
            'sort_order'   => 'nullable|integer',
            'icon_image'   => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'is_active'    => 'nullable|boolean',
        ]);

        $destinationPath = public_path('uploads/customer_profile_icon');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $key = $request->filled('key') 
            ? Str::snake(trim($request->input('key'))) 
            : Str::snake(trim($request->input('title')));

        // Ensure key uniqueness
        $originalKey = $key;
        $counter = 1;
        while (CustomerProfileIcon::where('key', $key)->exists()) {
            $key = $originalKey . '_' . $counter;
            $counter++;
        }

        $iconPath = null;
        if ($request->hasFile('icon_image') && $request->file('icon_image')->isValid()) {
            $file = $request->file('icon_image');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $fileName = $key . '_' . time() . '_' . Str::random(6) . '.' . $extension;
            $file->move($destinationPath, $fileName);
            $iconPath = 'uploads/customer_profile_icon/' . $fileName;
        }

        $icon = CustomerProfileIcon::create([
            'key'               => $key,
            'title'             => trim($request->input('title')),
            'subtitle'          => $request->filled('subtitle') ? trim($request->input('subtitle')) : null,
            'category'          => $request->input('category', 'action_menu'),
            'icon_path'         => $iconPath,
            'default_icon_path' => $iconPath ?: 'uploads/customer_profile_icon/default_' . $key . '.png',
            'badge_text'        => $request->filled('badge_text') ? trim($request->input('badge_text')) : null,
            'badge_color'       => $request->filled('badge_color') ? trim($request->input('badge_color')) : null,
            'target_route'      => $request->filled('target_route') ? trim($request->input('target_route')) : $key,
            'sort_order'        => (int) ($request->input('sort_order', CustomerProfileIcon::max('sort_order') + 1)),
            'is_active'         => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        Cache::forget('api_customer_profile_icons_v1');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Profile Icon '{$icon->title}' created successfully!",
                'data'    => $icon,
            ]);
        }

        return redirect()->route('admin.customer-profile-icons.index')
            ->with('success', "Profile Icon '{$icon->title}' has been successfully created!");
    }

    /**
     * Update an existing Customer Profile Icon with uploaded image.
     */
    public function update(Request $request, $id)
    {
        $icon = CustomerProfileIcon::findOrFail($id);

        $request->validate([
            'title'        => 'required|string|max:100',
            'category'     => 'nullable|string|in:wallet_card,banner_card,action_menu',
            'subtitle'     => 'nullable|string|max:255',
            'badge_text'   => 'nullable|string|max:50',
            'badge_color'  => 'nullable|string|max:50',
            'target_route' => 'nullable|string|max:100',
            'sort_order'   => 'nullable|integer',
            'icon_image'   => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'is_active'    => 'nullable|boolean',
        ]);

        $destinationPath = public_path('uploads/customer_profile_icon');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        // Handle uploaded image
        if ($request->hasFile('icon_image') && $request->file('icon_image')->isValid()) {
            // Remove previous custom file if exists
            if (!empty($icon->icon_path)) {
                $oldPath = public_path($icon->icon_path);
                if (File::exists($oldPath) && !str_contains($oldPath, 'default_')) {
                    @File::delete($oldPath);
                }
            }

            $file = $request->file('icon_image');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $fileName = $icon->key . '_' . time() . '_' . Str::random(6) . '.' . $extension;
            $file->move($destinationPath, $fileName);

            $icon->icon_path = 'uploads/customer_profile_icon/' . $fileName;
        }

        $icon->title = trim($request->input('title'));
        if ($request->filled('category')) {
            $icon->category = $request->input('category');
        }
        $icon->subtitle = $request->filled('subtitle') ? trim($request->input('subtitle')) : null;
        $icon->badge_text = $request->filled('badge_text') ? trim($request->input('badge_text')) : null;
        $icon->badge_color = $request->filled('badge_color') ? trim($request->input('badge_color')) : $icon->badge_color;
        $icon->target_route = $request->filled('target_route') ? trim($request->input('target_route')) : $icon->target_route;
        if ($request->filled('sort_order')) {
            $icon->sort_order = (int) $request->input('sort_order');
        }
        $icon->is_active = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $icon->save();
        Cache::forget('api_customer_profile_icons_v1');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => "Profile Icon '{$icon->title}' updated successfully!",
                'icon_url' => $icon->icon_url,
                'data'     => $icon,
            ]);
        }

        return redirect()->route('admin.customer-profile-icons.index')
            ->with('success', "Profile Icon '{$icon->title}' has been successfully updated!");
    }

    /**
     * Delete an icon.
     */
    public function destroy(Request $request, $id)
    {
        $icon = CustomerProfileIcon::findOrFail($id);
        
        if (!empty($icon->icon_path)) {
            $oldPath = public_path($icon->icon_path);
            if (File::exists($oldPath) && !str_contains($oldPath, 'default_')) {
                @File::delete($oldPath);
            }
        }

        $title = $icon->title;
        $icon->delete();
        Cache::forget('api_customer_profile_icons_v1');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Profile Icon '{$title}' deleted successfully!",
            ]);
        }

        return redirect()->route('admin.customer-profile-icons.index')
            ->with('success', "Profile Icon '{$title}' has been deleted!");
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Request $request, $id)
    {
        $icon = CustomerProfileIcon::findOrFail($id);
        $icon->is_active = !$icon->is_active;
        $icon->save();
        Cache::forget('api_customer_profile_icons_v1');

        return response()->json([
            'success'   => true,
            'is_active' => $icon->is_active,
            'message'   => "Status updated to " . ($icon->is_active ? 'Active' : 'Inactive'),
        ]);
    }

    /**
     * Initialize 10 standard slots for the "Me" Screen so admin can simply upload pictures.
     */
    public function initializeSlots(Request $request)
    {
        $standardSlots = [
            [
                'key'               => 'my_gems',
                'title'             => 'My Gems',
                'subtitle'          => 'User Diamond & Gem Balance',
                'category'          => 'wallet_card',
                'default_icon_path' => 'uploads/customer_profile_icon/default_my_gems.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'wallet/gems',
                'sort_order'        => 1,
                'is_active'         => true,
            ],
            [
                'key'               => 'beans_center',
                'title'             => 'Beans Center',
                'subtitle'          => 'Beans & Earnings Exchange',
                'category'          => 'wallet_card',
                'default_icon_path' => 'uploads/customer_profile_icon/default_beans_center.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'wallet/beans',
                'sort_order'        => 2,
                'is_active'         => true,
            ],
            [
                'key'               => 'spend_less_card',
                'title'             => 'Spend Less, Get More Gems!',
                'subtitle'          => 'Update to New User Weekly Card',
                'category'          => 'banner_card',
                'default_icon_path' => 'uploads/customer_profile_icon/default_spend_less_card.png',
                'badge_text'        => 'big discount',
                'badge_color'       => '#FEF08A',
                'target_route'      => 'wallet/spend_less',
                'sort_order'        => 3,
                'is_active'         => true,
            ],
            [
                'key'               => 'svip',
                'title'             => 'SVIP',
                'subtitle'          => 'Premium VIP Membership & Privileges',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_svip.png',
                'badge_text'        => 'VIP',
                'badge_color'       => '#FCD34D',
                'target_route'      => 'vip',
                'sort_order'        => 4,
                'is_active'         => true,
            ],
            [
                'key'               => 'my_bag',
                'title'             => 'My Bag',
                'subtitle'          => 'Inventory: Frames, Rides, Chat Bubbles',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_my_bag.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'bag',
                'sort_order'        => 5,
                'is_active'         => true,
            ],
            [
                'key'               => 'gems_center',
                'title'             => 'Gems Center',
                'subtitle'          => 'Purchase & Top-Up Coin Packages',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_gems_center.png',
                'badge_text'        => 'HOT',
                'badge_color'       => '#F87171',
                'target_route'      => 'gems',
                'sort_order'        => 6,
                'is_active'         => true,
            ],
            [
                'key'               => 'payment_details',
                'title'             => 'Payment details',
                'subtitle'          => 'Payment Methods & Banking Details',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_payment_details.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'payments',
                'sort_order'        => 7,
                'is_active'         => true,
            ],
            [
                'key'               => 'my_level',
                'title'             => 'My Level',
                'subtitle'          => 'User Charm & Experience Level Progression',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_my_level.png',
                'badge_text'        => 'Lv1',
                'badge_color'       => '#818CF8',
                'target_route'      => 'level',
                'sort_order'        => 8,
                'is_active'         => true,
            ],
            [
                'key'               => 'sign_in',
                'title'             => 'Sign-In',
                'subtitle'          => 'Daily Login Check-in & Rewards',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_sign_in.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'checkin',
                'sort_order'        => 9,
                'is_active'         => true,
            ],
            [
                'key'               => 'reward',
                'title'             => 'Reward',
                'subtitle'          => 'Tasks, Quests & Milestone Gifts',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_reward.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'rewards',
                'sort_order'        => 10,
                'is_active'         => true,
            ],
        ];

        foreach ($standardSlots as $slot) {
            CustomerProfileIcon::firstOrCreate(
                ['key' => $slot['key']],
                $slot
            );
        }

        Cache::forget('api_customer_profile_icons_v1');

        return redirect()->route('admin.customer-profile-icons.index')
            ->with('success', '10 standard Customer Profile Slots created! You can now upload your custom pictures.');
    }

    /**
     * Reset a custom icon back to default built-in image.
     */
    public function resetDefault(Request $request, $id)
    {
        $icon = CustomerProfileIcon::findOrFail($id);

        if (!empty($icon->icon_path)) {
            $oldPath = public_path($icon->icon_path);
            if (File::exists($oldPath) && !str_contains($oldPath, 'default_')) {
                @File::delete($oldPath);
            }
            $icon->icon_path = null;
            $icon->save();
            Cache::forget('api_customer_profile_icons_v1');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => "Icon '{$icon->title}' reset to default picture!",
                'icon_url' => $icon->icon_url,
            ]);
        }

        return redirect()->route('admin.customer-profile-icons.index')
            ->with('success', "Icon '{$icon->title}' has been reset to default image!");
    }

    /**
     * Bulk update status or routes.
     */
    public function bulkUpdate(Request $request)
    {
        $statusMap = $request->input('status', []);

        foreach ($statusMap as $id => $statusVal) {
            CustomerProfileIcon::where('id', $id)->update([
                'is_active' => (bool) $statusVal,
            ]);
        }

        Cache::forget('api_customer_profile_icons_v1');

        return redirect()->route('admin.customer-profile-icons.index')
            ->with('success', 'Profile Icons configuration synchronized successfully!');
    }
}
