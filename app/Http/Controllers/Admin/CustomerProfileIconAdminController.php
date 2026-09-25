<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfileIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CustomerProfileIconAdminController extends Controller
{
    /**
     * Display listing of all 10 Customer Profile Icons for the "Me" Screen.
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
     * Update an existing Customer Profile Icon with uploaded image.
     */
    public function update(Request $request, $id)
    {
        $icon = CustomerProfileIcon::findOrFail($id);

        $request->validate([
            'title'        => 'required|string|max:100',
            'subtitle'     => 'nullable|string|max:255',
            'badge_text'   => 'nullable|string|max:50',
            'target_route' => 'nullable|string|max:100',
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
        $icon->subtitle = $request->filled('subtitle') ? trim($request->input('subtitle')) : null;
        $icon->badge_text = $request->filled('badge_text') ? trim($request->input('badge_text')) : null;
        $icon->target_route = $request->filled('target_route') ? trim($request->input('target_route')) : $icon->target_route;
        $icon->is_active = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $icon->save();

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

        return redirect()->route('admin.customer-profile-icons.index')
            ->with('success', 'Profile Icons configuration synchronized successfully!');
    }
}
