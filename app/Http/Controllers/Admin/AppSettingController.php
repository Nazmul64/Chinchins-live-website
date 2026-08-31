<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AppSettingController extends Controller
{
    /**
     * Display general app settings page.
     */
    public function index()
    {
        $settings = AppSetting::all()->pluck('value', 'key')->toArray();
        $defaults = AppSetting::defaults();
        $merged = array_merge($defaults, $settings);

        return view('admin.settings.index', compact('merged'));
    }

    /**
     * Update app branding and general settings.
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'app_name'            => 'required|string|max:100',
                'app_tagline'         => 'nullable|string|max:200',
                'app_version'         => 'nullable|string|max:20',
                'free_messages_limit' => 'nullable|integer|min:0|max:100',
                'message_coin_cost'   => 'nullable|integer|min:0',
                'app_logo_file'       => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
                'app_icon_file'       => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            ]);

            AppSetting::set('app_name', $request->input('app_name'), 'branding', 'Mobile App Name');
            AppSetting::set('app_tagline', $request->input('app_tagline'), 'branding', 'App Tagline');
            AppSetting::set('app_version', $request->input('app_version') ?: '1.0.0', 'general', 'App Version');
            AppSetting::set('free_messages_limit', $request->input('free_messages_limit') ?: '5', 'chat', 'Free messages limit');
            AppSetting::set('message_coin_cost', $request->input('message_coin_cost') ?: '5', 'chat', 'Coin cost per message');

            $uploadDir = public_path('uploads/app');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0777, true, true);
            }

            // Handle App Logo Upload
            if ($request->hasFile('app_logo_file')) {
                $file = $request->file('app_logo_file');
                $filename = 'app_logo_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                AppSetting::set('app_logo', 'uploads/app/' . $filename, 'branding', 'App Logo');
            }

            // Handle App Icon Upload
            if ($request->hasFile('app_icon_file')) {
                $file = $request->file('app_icon_file');
                $filename = 'app_icon_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                AppSetting::set('app_icon', 'uploads/app/' . $filename, 'branding', 'App Icon');
            }

            return back()->with('success', 'App branding & settings updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage())->withInput();
        }
    }
}
