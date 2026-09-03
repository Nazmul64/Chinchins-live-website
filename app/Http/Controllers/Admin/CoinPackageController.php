<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinPackage;
use Illuminate\Http\Request;

class CoinPackageController extends Controller
{
    /**
     * Display a listing of coin packages.
     */
    public function index()
    {
        $packages = CoinPackage::orderBy('sort_order')->latest()->get();
        return view('admin.coin-packages.index', compact('packages'));
    }

    /**
     * Store a newly created coin package.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'          => 'nullable|string|max:100',
                'coins'          => 'required|integer|min:1',
                'bonus_coins'    => 'nullable|integer|min:0',
                'price'          => 'required|numeric|min:1',
                'currency'       => 'nullable|string|max:10',
                'badge'          => 'nullable|string|max:50',
                'badge_color'    => 'nullable|string|max:30',
                'icon'           => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'icon_url'       => 'nullable|string|max:255',
                'animation_file' => 'nullable|file|max:25600',
                'animation_url'  => 'nullable|string|max:255',
                'format'         => 'nullable|string|in:svga,lottie,webp,gif,image,mp4',
                'is_popular'     => 'nullable|boolean',
                'is_active'      => 'nullable|boolean',
                'sort_order'     => 'nullable|integer|min:0',
            ]);

            $iconPath = $request->input('icon_url');
            if ($request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $destPath = public_path('uploads/coins/icons');
                if (!file_exists($destPath)) {
                    @mkdir($destPath, 0777, true);
                }
                $filename = 'coin_icon_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $iconFile->getClientOriginalExtension();
                $iconFile->move($destPath, $filename);
                $iconPath = 'uploads/coins/icons/' . $filename;
            }

            $animationPath = $request->input('animation_url');
            $format = $request->input('format', 'image');
            if ($request->hasFile('animation_file')) {
                $animFile = $request->file('animation_file');
                $destPath = public_path('uploads/coins/animations');
                if (!file_exists($destPath)) {
                    @mkdir($destPath, 0777, true);
                }
                $ext = strtolower($animFile->getClientOriginalExtension());
                $filename = 'coin_anim_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $ext;
                $animFile->move($destPath, $filename);
                $animationPath = 'uploads/coins/animations/' . $filename;
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

            $validated['title'] = $request->input('title') ?: ($request->input('coins') . ' Gems Pack');
            $validated['currency'] = $request->input('currency', 'BDT');
            $validated['icon_url'] = $iconPath;
            $validated['animation_url'] = $animationPath;
            $validated['format'] = $format;
            $validated['bonus_coins'] = (int) ($request->input('bonus_coins') ?: 0);
            $validated['is_popular'] = $request->has('is_popular');
            $validated['is_active'] = $request->has('is_active');
            $validated['sort_order'] = (int) ($request->input('sort_order') ?: 0);
            $validated['badge_color'] = $request->input('badge_color') ?: 'pink';

            CoinPackage::create($validated);

            return redirect()->route('admin.coin-packages.index')->with('success', 'Coin Package created successfully!');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not save coin package: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified coin package.
     */
    public function update(Request $request, $id)
    {
        try {
            $package = CoinPackage::findOrFail($id);

            $validated = $request->validate([
                'title'          => 'nullable|string|max:100',
                'coins'          => 'required|integer|min:1',
                'bonus_coins'    => 'nullable|integer|min:0',
                'price'          => 'required|numeric|min:1',
                'currency'       => 'nullable|string|max:10',
                'badge'          => 'nullable|string|max:50',
                'badge_color'    => 'nullable|string|max:30',
                'icon'           => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
                'icon_url'       => 'nullable|string|max:255',
                'animation_file' => 'nullable|file|max:25600',
                'animation_url'  => 'nullable|string|max:255',
                'format'         => 'nullable|string|in:svga,lottie,webp,gif,image,mp4',
                'is_popular'     => 'nullable|boolean',
                'is_active'      => 'nullable|boolean',
                'sort_order'     => 'nullable|integer|min:0',
            ]);

            if ($request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $destPath = public_path('uploads/coins/icons');
                if (!file_exists($destPath)) {
                    @mkdir($destPath, 0777, true);
                }
                $filename = 'coin_icon_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $iconFile->getClientOriginalExtension();
                $iconFile->move($destPath, $filename);
                $package->icon_url = 'uploads/coins/icons/' . $filename;
            } elseif ($request->filled('icon_url')) {
                $package->icon_url = $request->input('icon_url');
            }

            if ($request->hasFile('animation_file')) {
                $animFile = $request->file('animation_file');
                $destPath = public_path('uploads/coins/animations');
                if (!file_exists($destPath)) {
                    @mkdir($destPath, 0777, true);
                }
                $ext = strtolower($animFile->getClientOriginalExtension());
                $filename = 'coin_anim_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $ext;
                $animFile->move($destPath, $filename);
                $package->animation_url = 'uploads/coins/animations/' . $filename;
                if ($ext === 'svga') {
                    $package->format = 'svga';
                } elseif (in_array($ext, ['json', 'lottie'])) {
                    $package->format = 'lottie';
                } elseif ($ext === 'webp') {
                    $package->format = 'webp';
                } elseif ($ext === 'gif') {
                    $package->format = 'gif';
                }
            } elseif ($request->filled('animation_url')) {
                $package->animation_url = $request->input('animation_url');
            }

            if ($request->filled('format')) {
                $package->format = $request->input('format');
            }

            if ($request->filled('title')) $package->title = $request->input('title');
            if ($request->filled('currency')) $package->currency = $request->input('currency');
            $package->coins = (int) $request->input('coins');
            $package->bonus_coins = (int) ($request->input('bonus_coins') ?: 0);
            $package->price = (float) $request->input('price');
            $package->badge = $request->input('badge') ?: null;
            $package->badge_color = $request->input('badge_color') ?: 'pink';
            $package->is_popular = $request->has('is_popular');
            $package->is_active = $request->has('is_active');
            $package->sort_order = (int) ($request->input('sort_order') ?: 0);

            $package->save();

            return redirect()->route('admin.coin-packages.index')->with('success', 'Coin Package updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not update coin package: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $package = CoinPackage::findOrFail($id);
        $package->is_active = !$package->is_active;
        $package->save();

        $statusStr = $package->is_active ? 'Activated' : 'Disabled';
        return back()->with('success', "Package of {$package->total_coins} Coins has been {$statusStr}.");
    }

    /**
     * Delete coin package.
     */
    public function destroy($id)
    {
        $package = CoinPackage::findOrFail($id);
        $package->delete();

        return redirect()->route('admin.coin-packages.index')->with('success', 'Coin Package removed successfully.');
    }
}
