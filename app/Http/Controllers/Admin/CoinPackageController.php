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
                'coins' => 'required|integer|min:1',
                'bonus_coins' => 'nullable|integer|min:0',
                'price' => 'required|numeric|min:1',
                'badge' => 'nullable|string|max:50',
                'badge_color' => 'nullable|string|max:30',
                'is_popular' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

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
                'coins' => 'required|integer|min:1',
                'bonus_coins' => 'nullable|integer|min:0',
                'price' => 'required|numeric|min:1',
                'badge' => 'nullable|string|max:50',
                'badge_color' => 'nullable|string|max:30',
                'is_popular' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $validated['bonus_coins'] = (int) ($request->input('bonus_coins') ?: 0);
            $validated['is_popular'] = $request->has('is_popular');
            $validated['is_active'] = $request->has('is_active');
            $validated['sort_order'] = (int) ($request->input('sort_order') ?: 0);
            $validated['badge_color'] = $request->input('badge_color') ?: 'pink';

            $package->update($validated);

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
