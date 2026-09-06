<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BagItem;
use App\Models\User;
use App\Models\UserBagItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BagAdminController extends Controller
{
    /**
     * Display My Bag items catalog & inventory dashboard.
     */
    public function index(Request $request)
    {
        BagItem::seedDefaultItems();

        $category = $request->input('category', 'all');
        $search = trim($request->input('search', ''));

        $query = BagItem::query();

        if ($category !== 'all' && in_array($category, array_keys(BagItem::categories()))) {
            $query->where('category', $category);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Statistics
        $totalItems = BagItem::count();
        $activeItems = BagItem::where('is_active', true)->count();
        $categoryCounts = BagItem::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $totalUserItems = UserBagItem::count();
        $activeEquippedTotal = UserBagItem::where('is_equipped', true)->count();

        $categories = BagItem::categories();
        $categoryIcons = BagItem::categoryIcons();

        return view('admin.bag.index', compact(
            'items',
            'category',
            'search',
            'totalItems',
            'activeItems',
            'categoryCounts',
            'totalUserItems',
            'activeEquippedTotal',
            'categories',
            'categoryIcons'
        ));
    }

    /**
     * Store a newly created Bag Item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'category'         => 'required|string|in:coupon,avatar_frame,chat_style,profile_card,entrance_bubble,big_entrance',
            'code'             => 'nullable|string|max:100|unique:bag_items,code',
            'price_coins'      => 'nullable|integer|min:0',
            'price_bdt'        => 'nullable|numeric|min:0',
            'duration_days'    => 'nullable|integer|min:0',
            'badge'            => 'nullable|string|max:50',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'coupon_value'     => 'nullable|integer|min:0',
            'icon_file'        => 'nullable|file|mimes:svg,png,jpg,jpeg,webp,gif|max:10240',
            'icon_url'         => 'nullable|string|max:255',
            'format'           => 'nullable|string|in:svg,svga,lottie,webp,png,mp4',
            'effect_type'      => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:1000',
            'is_active'        => 'nullable|boolean',
            'is_giftable'      => 'nullable|boolean',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $code = !empty($validated['code']) 
            ? Str::slug($validated['code'], '_') 
            : Str::slug($validated['category'] . '_' . $validated['name'], '_') . '_' . rand(100, 999);

        $iconPath = $validated['icon_url'] ?? null;

        if ($request->hasFile('icon_file')) {
            $file = $request->file('icon_file');
            $uploadDir = public_path('uploads/my_bag');
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $filename = 'bag_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $iconPath = 'uploads/my_bag/' . $filename;
        }

        BagItem::create([
            'name'             => $validated['name'],
            'category'         => $validated['category'],
            'code'             => $code,
            'price_coins'      => (int) ($validated['price_coins'] ?? 0),
            'price_bdt'        => (float) ($validated['price_bdt'] ?? 0.00),
            'duration_days'    => (int) ($validated['duration_days'] ?? 7),
            'badge'            => $validated['badge'] ?: null,
            'discount_percent' => !empty($validated['discount_percent']) ? (int) $validated['discount_percent'] : null,
            'coupon_value'     => !empty($validated['coupon_value']) ? (int) $validated['coupon_value'] : null,
            'icon_url'         => $iconPath,
            'image_url'        => $iconPath,
            'preview_url'      => $iconPath,
            'format'           => $validated['format'] ?? 'svg',
            'effect_type'      => $validated['effect_type'] ?: null,
            'description'      => $validated['description'] ?: null,
            'is_active'        => $request->boolean('is_active', true),
            'is_giftable'      => $request->boolean('is_giftable', true),
            'sort_order'       => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.my-bag.index', ['category' => $validated['category']])
            ->with('success', "Item '{$validated['name']}' created successfully in {$validated['category']}!");
    }

    /**
     * Update an existing Bag Item.
     */
    public function update(Request $request, int $id)
    {
        $item = BagItem::findOrFail($id);

        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'category'         => 'required|string|in:coupon,avatar_frame,chat_style,profile_card,entrance_bubble,big_entrance',
            'price_coins'      => 'nullable|integer|min:0',
            'price_bdt'        => 'nullable|numeric|min:0',
            'duration_days'    => 'nullable|integer|min:0',
            'badge'            => 'nullable|string|max:50',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'coupon_value'     => 'nullable|integer|min:0',
            'icon_file'        => 'nullable|file|mimes:svg,png,jpg,jpeg,webp,gif|max:10240',
            'icon_url'         => 'nullable|string|max:255',
            'format'           => 'nullable|string|in:svg,svga,lottie,webp,png,mp4',
            'effect_type'      => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:1000',
            'is_active'        => 'nullable|boolean',
            'is_giftable'      => 'nullable|boolean',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('icon_file')) {
            $file = $request->file('icon_file');
            $uploadDir = public_path('uploads/my_bag');
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $filename = 'bag_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $item->icon_url = 'uploads/my_bag/' . $filename;
            $item->image_url = 'uploads/my_bag/' . $filename;
            $item->preview_url = 'uploads/my_bag/' . $filename;
        } elseif ($request->filled('icon_url')) {
            $item->icon_url = $request->input('icon_url');
            $item->image_url = $request->input('icon_url');
            $item->preview_url = $request->input('icon_url');
        }

        $item->name = $validated['name'];
        $item->category = $validated['category'];
        $item->price_coins = (int) ($validated['price_coins'] ?? 0);
        $item->price_bdt = (float) ($validated['price_bdt'] ?? 0.00);
        $item->duration_days = (int) ($validated['duration_days'] ?? 7);
        $item->badge = $validated['badge'] ?: null;
        $item->discount_percent = !empty($validated['discount_percent']) ? (int) $validated['discount_percent'] : null;
        $item->coupon_value = !empty($validated['coupon_value']) ? (int) $validated['coupon_value'] : null;
        $item->format = $validated['format'] ?? $item->format;
        $item->effect_type = $validated['effect_type'] ?: null;
        $item->description = $validated['description'] ?: null;
        $item->is_active = $request->boolean('is_active', true);
        $item->is_giftable = $request->boolean('is_giftable', true);
        $item->sort_order = (int) ($validated['sort_order'] ?? 0);

        $item->save();

        return redirect()->back()->with('success', "Item '{$item->name}' updated successfully!");
    }

    /**
     * Delete a Bag Item.
     */
    public function destroy(int $id)
    {
        $item = BagItem::findOrFail($id);
        $name = $item->name;
        $item->delete();

        return redirect()->back()->with('success', "Item '{$name}' removed from My Bag catalog.");
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(int $id)
    {
        $item = BagItem::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();

        $statusText = $item->is_active ? 'Activated' : 'Deactivated';
        return redirect()->back()->with('success', "Item '{$item->name}' is now {$statusText}.");
    }

    /**
     * Give/Grant a bag item directly to a user from Admin Panel.
     */
    public function giveToUser(Request $request)
    {
        $validated = $request->validate([
            'bag_item_id'   => 'required|exists:bag_items,id',
            'user_identity' => 'required|string', // ID, Account ID, or Email/Phone
            'quantity'      => 'nullable|integer|min:1',
            'duration_days' => 'nullable|integer|min:0', // 0 = inherit from item or permanent
        ]);

        $item = BagItem::findOrFail($validated['bag_item_id']);
        $identity = trim($validated['user_identity']);

        $user = User::where('id', $identity)
            ->orWhere('account_id', $identity)
            ->orWhere('phone', $identity)
            ->orWhere('email', $identity)
            ->first();

        if (!$user) {
            return redirect()->back()->with('error', "User not found with ID/Account ID: {$identity}");
        }

        $duration = $request->filled('duration_days') && (int) $request->duration_days >= 0
            ? (int) $request->duration_days
            : $item->duration_days;

        $now = Carbon::now();
        $expiresAt = $duration > 0 ? $now->copy()->addDays($duration) : null;
        $quantity = (int) ($validated['quantity'] ?? 1);

        UserBagItem::create([
            'user_id'       => $user->id,
            'bag_item_id'   => $item->id,
            'quantity'      => $quantity,
            'status'        => 'unused',
            'is_equipped'   => false,
            'acquired_from' => 'admin',
            'sender_id'     => null,
            'started_at'    => $now,
            'expires_at'    => $expiresAt,
            'metadata'      => [
                'granted_by' => 'admin',
                'granted_at' => $now->toIso8601String(),
                'item_name'  => $item->name,
            ],
        ]);

        return redirect()->back()->with('success', "Successfully gifted {$quantity}x {$item->name} to user {$user->display_name} ({$user->display_id})!");
    }

    /**
     * View all user inventory allocations.
     */
    public function userInventory(Request $request)
    {
        $query = UserBagItem::with(['user', 'bagItem', 'sender'])->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('bagItem', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->whereHas('user', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('nickname', 'like', "%{$s}%")
                  ->orWhere('account_id', 'like', "%{$s}%");
            });
        }

        $userItems = $query->paginate(20)->withQueryString();
        $categories = BagItem::categories();

        return view('admin.bag.user_inventory', compact('userItems', 'categories'));
    }
}
