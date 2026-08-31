<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GiftController extends Controller
{
    /**
     * Display a listing of gifts with dashboard statistics.
     */
    public function index(Request $request)
    {
        $query = Gift::query();

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('badge', 'LIKE', "%{$s}%")
                  ->orWhere('category', 'LIKE', "%{$s}%")
                  ->orWhere('coins', 'LIKE', "%{$s}%");
            });
        }

        $gifts = $query->orderBy('sort_order')->orderBy('coins', 'desc')->paginate(24);

        // Calculate platform statistics
        $totalGiftsCount = Gift::count();
        $activeGiftsCount = Gift::where('is_active', true)->count();
        $totalSentCount = UserGift::sum('quantity') ?: 0;
        $totalSentCoins = UserGift::sum('total_coins') ?: 0;

        // Categories list
        $categories = ['popular', 'luxury', 'romantic', 'effects', 'vip'];

        // Users list for direct gift awarding tool
        $users = User::orderBy('name')->take(50)->get();

        return view('admin.gifts.index', compact(
            'gifts',
            'totalGiftsCount',
            'activeGiftsCount',
            'totalSentCount',
            'totalSentCoins',
            'categories',
            'users'
        ));
    }

    /**
     * Store a newly created gift in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'           => 'required|string|max:100',
                'coins'          => 'required|integer|min:1',
                'category'       => 'required|string|max:50',
                'badge'          => 'nullable|string|max:30',
                'sort_order'     => 'nullable|integer|min:0',
                'is_active'      => 'nullable|boolean',
                'is_broadcast'   => 'nullable|boolean',
                'description'    => 'nullable|string|max:500',
                'image_file'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'image_url'      => 'nullable|string|max:255',
                'animation_url'  => 'nullable|string|max:255',
                'animation_type' => 'nullable|string|max:30',
                'sound_url'      => 'nullable|string|max:255',
            ]);

            $imagePath = 'uploads/gifts/rose_bouquet.png';

            // Handle Image Upload
            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $destinationPath = public_path('uploads/gifts');
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $filename = 'gift_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $imagePath = 'uploads/gifts/' . $filename;
            } elseif ($request->filled('image_url')) {
                $imagePath = trim($request->input('image_url'));
            }

            Gift::create([
                'name'           => $validated['name'],
                'coins'          => (int) $validated['coins'],
                'category'       => strtolower($validated['category']),
                'badge'          => $validated['badge'] ? strtoupper($validated['badge']) : null,
                'sort_order'     => (int) ($request->input('sort_order') ?: 0),
                'is_active'      => $request->has('is_active') ? 1 : 0,
                'is_broadcast'   => $request->has('is_broadcast') ? 1 : 0,
                'description'    => $validated['description'] ?? null,
                'image'          => $imagePath,
                'animation_url'  => $request->input('animation_url'),
                'animation_type' => $request->input('animation_type') ?: 'image',
                'sound_url'      => $request->input('sound_url'),
            ]);

            return redirect()->route('admin.gifts.index')->with('success', "Gift \"{$validated['name']}\" added successfully!");
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not create gift: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified gift in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $gift = Gift::findOrFail($id);

            $validated = $request->validate([
                'name'           => 'required|string|max:100',
                'coins'          => 'required|integer|min:1',
                'category'       => 'required|string|max:50',
                'badge'          => 'nullable|string|max:30',
                'sort_order'     => 'nullable|integer|min:0',
                'is_active'      => 'nullable|boolean',
                'is_broadcast'   => 'nullable|boolean',
                'description'    => 'nullable|string|max:500',
                'image_file'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'image_url'      => 'nullable|string|max:255',
                'animation_url'  => 'nullable|string|max:255',
                'animation_type' => 'nullable|string|max:30',
                'sound_url'      => 'nullable|string|max:255',
            ]);

            $imagePath = $gift->image;

            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $destinationPath = public_path('uploads/gifts');
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $filename = 'gift_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $imagePath = 'uploads/gifts/' . $filename;
            } elseif ($request->filled('image_url')) {
                $imagePath = trim($request->input('image_url'));
            }

            $gift->update([
                'name'           => $validated['name'],
                'coins'          => (int) $validated['coins'],
                'category'       => strtolower($validated['category']),
                'badge'          => $validated['badge'] ? strtoupper($validated['badge']) : null,
                'sort_order'     => (int) ($request->input('sort_order') ?: 0),
                'is_active'      => $request->has('is_active') ? 1 : 0,
                'is_broadcast'   => $request->has('is_broadcast') ? 1 : 0,
                'description'    => $validated['description'] ?? null,
                'image'          => $imagePath,
                'animation_url'  => $request->input('animation_url'),
                'animation_type' => $request->input('animation_type') ?: 'image',
                'sound_url'      => $request->input('sound_url'),
            ]);

            return redirect()->route('admin.gifts.index')->with('success', "Gift \"{$gift->name}\" updated successfully!");
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not update gift: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus($id)
    {
        $gift = Gift::findOrFail($id);
        $gift->is_active = !$gift->is_active;
        $gift->save();

        $statusStr = $gift->is_active ? 'Activated' : 'Deactivated';
        return back()->with('success', "Gift \"{$gift->name}\" has been {$statusStr}.");
    }

    /**
     * Delete the specified gift.
     */
    public function destroy($id)
    {
        $gift = Gift::findOrFail($id);
        $name = $gift->name;
        $gift->delete();

        return redirect()->route('admin.gifts.index')->with('success', "Gift \"{$name}\" removed successfully.");
    }

    /**
     * Direct Admin Tool: Award/Give a gift to a user.
     */
    public function giveGiftToUser(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'gift_id'  => 'required|exists:gifts,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        $user = User::findOrFail($request->user_id);
        $gift = Gift::findOrFail($request->gift_id);
        $quantity = (int) $request->quantity;
        $totalCoins = $gift->coins * $quantity;

        UserGift::create([
            'user_id'        => $user->id,
            'sender_id'      => auth()->id() ?? 1,
            'gift_id'        => $gift->id,
            'quantity'       => $quantity,
            'coins_per_unit' => $gift->coins,
            'total_coins'    => $totalCoins,
            'context'        => 'admin_reward',
        ]);

        return back()->with('success', "Successfully awarded {$quantity}x {$gift->name} to {$user->display_name}!");
    }

    /**
     * View received gifts transactions log.
     */
    public function logs(Request $request)
    {
        $logs = UserGift::with(['user', 'sender', 'gift'])
            ->latest()
            ->paginate(25);

        return view('admin.gifts.logs', compact('logs'));
    }
}
