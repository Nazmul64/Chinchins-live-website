<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CharmLevelSetting;
use App\Models\Gift;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GiftController extends Controller
{
    /**
     * Parse human input for coins/diamonds (e.g. 17.70, 17.70K, 17700, 500, 9.99k).
     */
    public static function parseCoins($input): int
    {
        if (empty($input)) return 0;

        $str = strtoupper(trim((string) $input));
        $str = str_replace(['💎', ' ', ','], '', $str);

        if (str_ends_with($str, 'M')) {
            $num = (float) str_replace('M', '', $str);
            return (int) round($num * 1000000);
        }

        if (str_ends_with($str, 'K')) {
            $num = (float) str_replace('K', '', $str);
            return (int) round($num * 1000);
        }

        if (is_numeric($str)) {
            $val = (float) $str;
            if ($val > 0 && $val < 1000 && str_contains($str, '.')) {
                return (int) round($val * 1000);
            }
            return (int) round($val);
        }

        return (int) round((float) preg_replace('/[^0-9.]/', '', $str));
    }

    /**
     * Display a listing of gifts with dashboard statistics and level settings.
     */
    public function index(Request $request)
    {
        // Seed default 27+ live animated gifts if missing
        Gift::seedDefaultGifts();

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

        // Charm Level Settings
        $levelSettings = CharmLevelSetting::orderBy('level', 'asc')->get();

        return view('admin.gifts.index', compact(
            'gifts',
            'totalGiftsCount',
            'activeGiftsCount',
            'totalSentCount',
            'totalSentCoins',
            'categories',
            'users',
            'levelSettings'
        ));
    }

    /**
     * Store a newly created gift in storage (Web Admin Form & API).
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'           => 'required|string|max:100',
                'coins'          => 'nullable',
                'coin_price'     => 'nullable',
                'category'       => 'nullable|string|max:50',
                'badge'          => 'nullable|string|max:30',
                'is_active'      => 'nullable|boolean',
                'is_broadcast'   => 'nullable|boolean',
                'image_file'     => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'icon'           => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'image_url'      => 'nullable|string|max:255',
                'animation_file' => 'nullable|file|max:25600', // SVGA / JSON Lottie up to 25MB
                'animation_url'  => 'nullable|string|max:255',
                'animation_type' => 'nullable|string|max:30',
                'format'         => 'nullable|string|in:svga,lottie,webp,image',
                'display_type'   => 'nullable|string|in:fullscreen,bubble',
                'sound_url'      => 'nullable|string|max:255',
            ]);

            $coinsInput = $request->input('coin_price') ?: $request->input('coins');
            $parsedCoins = static::parseCoins($coinsInput);
            if ($parsedCoins <= 0) {
                $parsedCoins = 100;
            }

            $iconPath = 'uploads/gifts/rose_bouquet.png';

            // 1. Handle Icon/Image File Upload
            $iconFile = $request->file('icon') ?: $request->file('image_file');
            if ($iconFile) {
                $destinationPath = public_path('uploads/gifts/icons');
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $filename = 'icon_' . time() . '_' . Str::random(8) . '.' . $iconFile->getClientOriginalExtension();
                $iconFile->move($destinationPath, $filename);
                $iconPath = 'uploads/gifts/icons/' . $filename;
            } elseif ($request->filled('image_url')) {
                $iconPath = trim($request->input('image_url'));
            }

            // 2. Handle Animation File (SVGA / Lottie JSON) Upload
            $animationPath = $request->input('animation_url');
            $format = $request->input('format', 'svga');

            if ($request->hasFile('animation_file')) {
                $animFile = $request->file('animation_file');
                $animDestPath = public_path('uploads/gifts/animations');
                if (!File::exists($animDestPath)) {
                    File::makeDirectory($animDestPath, 0777, true, true);
                }

                $ext = strtolower($animFile->getClientOriginalExtension());
                $animFilename = 'anim_' . time() . '_' . Str::random(8) . '.' . $ext;
                $animFile->move($animDestPath, $animFilename);
                $animationPath = 'uploads/gifts/animations/' . $animFilename;

                if ($ext === 'svga') {
                    $format = 'svga';
                } elseif (in_array($ext, ['json', 'lottie'])) {
                    $format = 'lottie';
                } elseif ($ext === 'webp') {
                    $format = 'webp';
                }
            }

            $displayType = $request->input('display_type') ?: ($request->has('is_broadcast') ? 'fullscreen' : 'bubble');

            // Auto sort order
            $nextSort = (Gift::max('sort_order') ?: 0) + 1;

            $gift = Gift::create([
                'name'           => $request->input('name'),
                'coins'          => $parsedCoins,
                'coin_price'     => $parsedCoins,
                'category'       => strtolower($request->input('category') ?: 'popular'),
                'badge'          => $request->filled('badge') ? strtoupper(trim($request->input('badge'))) : null,
                'sort_order'     => $nextSort,
                'is_active'      => $request->has('is_active') ? 1 : 0,
                'is_broadcast'   => $displayType === 'fullscreen' || $request->has('is_broadcast'),
                'description'    => null,
                'image'          => $iconPath,
                'icon_url'       => $iconPath,
                'animation_url'  => $animationPath,
                'file_url'       => $animationPath,
                'animation_type' => $format,
                'format'         => $format,
                'display_type'   => $displayType,
                'sound_url'      => $request->input('sound_url'),
            ]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Gift uploaded successfully!',
                    'data'    => $gift,
                ], 201);
            }

            return redirect()->route('admin.gifts.index')->with('success', "Gift \"{$gift->name}\" (💎 " . Gift::formatCoins($parsedCoins) . ") added successfully!");
        } catch (\Illuminate\Validation\ValidationException $ve) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['status' => false, 'errors' => $ve->validator->errors()], 422);
            }
            return back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Could not create gift: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * API Method: Store Gift via JSON / multipart API.
     */
    public function storeGift(Request $request): JsonResponse
    {
        return $this->store($request);
    }

    /**
     * Update the specified gift in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $gift = Gift::findOrFail($id);

            $request->validate([
                'name'           => 'required|string|max:100',
                'coins'          => 'nullable',
                'coin_price'     => 'nullable',
                'category'       => 'nullable|string|max:50',
                'badge'          => 'nullable|string|max:30',
                'is_active'      => 'nullable|boolean',
                'is_broadcast'   => 'nullable|boolean',
                'image_file'     => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'icon'           => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'image_url'      => 'nullable|string|max:255',
                'animation_file' => 'nullable|file|max:25600',
                'animation_url'  => 'nullable|string|max:255',
                'animation_type' => 'nullable|string|max:30',
                'format'         => 'nullable|string|in:svga,lottie,webp,image',
                'display_type'   => 'nullable|string|in:fullscreen,bubble',
                'sound_url'      => 'nullable|string|max:255',
            ]);

            $coinsInput = $request->input('coin_price') ?: $request->input('coins');
            $parsedCoins = static::parseCoins($coinsInput);
            if ($parsedCoins <= 0) {
                $parsedCoins = $gift->coins;
            }

            $iconPath = $gift->image;

            $iconFile = $request->file('icon') ?: $request->file('image_file');
            if ($iconFile) {
                $destinationPath = public_path('uploads/gifts/icons');
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }

                $filename = 'icon_' . time() . '_' . Str::random(8) . '.' . $iconFile->getClientOriginalExtension();
                $iconFile->move($destinationPath, $filename);
                $iconPath = 'uploads/gifts/icons/' . $filename;
            } elseif ($request->filled('image_url')) {
                $iconPath = trim($request->input('image_url'));
            }

            $animationPath = $gift->animation_url;
            $format = $request->input('format', $gift->format ?? 'svga');

            if ($request->hasFile('animation_file')) {
                $animFile = $request->file('animation_file');
                $animDestPath = public_path('uploads/gifts/animations');
                if (!File::exists($animDestPath)) {
                    File::makeDirectory($animDestPath, 0777, true, true);
                }

                $ext = strtolower($animFile->getClientOriginalExtension());
                $animFilename = 'anim_' . time() . '_' . Str::random(8) . '.' . $ext;
                $animFile->move($animDestPath, $animFilename);
                $animationPath = 'uploads/gifts/animations/' . $animFilename;

                if ($ext === 'svga') {
                    $format = 'svga';
                } elseif (in_array($ext, ['json', 'lottie'])) {
                    $format = 'lottie';
                }
            } elseif ($request->filled('animation_url')) {
                $animationPath = trim($request->input('animation_url'));
            }

            $displayType = $request->input('display_type', $gift->display_type ?? 'fullscreen');

            $gift->update([
                'name'           => $request->input('name'),
                'coins'          => $parsedCoins,
                'coin_price'     => $parsedCoins,
                'category'       => strtolower($request->input('category') ?: $gift->category),
                'badge'          => $request->filled('badge') ? strtoupper(trim($request->input('badge'))) : null,
                'is_active'      => $request->has('is_active') ? 1 : 0,
                'is_broadcast'   => $displayType === 'fullscreen' || $request->has('is_broadcast'),
                'image'          => $iconPath,
                'icon_url'       => $iconPath,
                'animation_url'  => $animationPath,
                'file_url'       => $animationPath,
                'animation_type' => $format,
                'format'         => $format,
                'display_type'   => $displayType,
                'sound_url'      => $request->input('sound_url'),
            ]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Gift updated successfully!',
                    'data'    => $gift,
                ]);
            }

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
     * Update Charm Level threshold configurations.
     */
    public function updateLevels(Request $request)
    {
        $levels = $request->input('levels', []);
        foreach ($levels as $levelNum => $data) {
            $requiredCoins = static::parseCoins($data['required_coins'] ?? ($levelNum * 10000));
            CharmLevelSetting::updateOrCreate(
                ['level' => (int) $levelNum],
                [
                    'title'          => $data['title'] ?? ('Level ' . $levelNum),
                    'required_coins' => $requiredCoins,
                    'badge_icon'     => $data['badge_icon'] ?? 'crown',
                    'badge_color'    => $data['badge_color'] ?? '#f59e0b',
                ]
            );
        }

        return back()->with('success', 'Charm Level coin thresholds updated successfully!');
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
