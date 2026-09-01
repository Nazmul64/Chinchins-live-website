<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AppVersion;
use App\Models\DeviceRegistration;
use App\Models\User;
use App\Services\PushNotificationService;
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

        $latestVersion = AppVersion::getLatest() ?? AppVersion::first();
        $allVersions = AppVersion::orderBy('version_code', 'desc')->get();
        $registeredDevicesCount = DeviceRegistration::where('is_active', true)->count();
        $totalPushTokensCount = User::whereNotNull('fcm_token')->count();

        return view('admin.settings.index', compact('merged', 'latestVersion', 'allVersions', 'registeredDevicesCount', 'totalPushTokensCount'));
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
                'support_email'       => 'nullable|string|max:100',
                'support_whatsapp'    => 'nullable|string|max:50',
                'fcm_server_key'      => 'nullable|string',
                'fcm_sender_id'       => 'nullable|string',
                'app_logo_file'       => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
                'app_icon_file'       => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            ]);

            AppSetting::set('app_name', $request->input('app_name'), 'branding', 'Mobile App Name');
            AppSetting::set('app_tagline', $request->input('app_tagline'), 'branding', 'App Tagline');
            AppSetting::set('app_version', $request->input('app_version') ?: '1.0.0', 'general', 'App Version');
            AppSetting::set('free_messages_limit', $request->input('free_messages_limit') ?: '5', 'chat', 'Free messages limit');
            AppSetting::set('message_coin_cost', $request->input('message_coin_cost') ?: '5', 'chat', 'Coin cost per message');
            AppSetting::set('support_email', $request->input('support_email', 'support@chinchins.live'), 'general', 'Support Email');
            AppSetting::set('support_whatsapp', $request->input('support_whatsapp', '+8801700000000'), 'general', 'Support WhatsApp');
            
            if ($request->has('fcm_server_key')) {
                AppSetting::set('fcm_server_key', $request->input('fcm_server_key'), 'push', 'Firebase FCM Server Key');
            }
            if ($request->has('fcm_sender_id')) {
                AppSetting::set('fcm_sender_id', $request->input('fcm_sender_id'), 'push', 'Firebase Sender ID');
            }

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

            return back()->with('success', 'App branding & global settings updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Publish or Update App Release Version for OTA in-app update.
     */
    public function publishVersion(Request $request)
    {
        try {
            $request->validate([
                'version_name'          => 'required|string|max:30',
                'version_code'          => 'required|integer|min:1',
                'min_supported_version' => 'nullable|string|max:30',
                'title'                 => 'required|string|max:150',
                'changelog'             => 'nullable|string',
                'download_url'          => 'nullable|string',
                'apk_file'              => 'nullable|file|max:102400', // max 100MB
            ]);

            $downloadUrl = $request->input('download_url');

            // Handle Direct APK file upload
            if ($request->hasFile('apk_file')) {
                $downloadsDir = public_path('downloads');
                if (!File::exists($downloadsDir)) {
                    File::makeDirectory($downloadsDir, 0777, true, true);
                }

                $apkFile = $request->file('apk_file');
                $apkName = 'chinchins_live_v' . $request->input('version_name') . '_' . time() . '.apk';
                $apkFile->move($downloadsDir, $apkName);
                $downloadUrl = asset('downloads/' . $apkName);
            }

            $remoteFlags = [
                'enable_video_calling'       => $request->has('enable_video_calling'),
                'enable_audio_calling'       => $request->has('enable_audio_calling'),
                'enable_random_matching'     => $request->has('enable_random_matching'),
                'enable_instant_call_wake'   => $request->has('enable_instant_call_wake'),
                'enable_push_notifications'  => $request->has('enable_push_notifications'),
                'enable_in_app_updates'      => $request->has('enable_in_app_updates'),
                'enable_profile_view_alert'  => $request->has('enable_profile_view_alert'),
                'enable_auto_chat_greetings' => $request->has('enable_auto_chat_greetings'),
                'maintenance_mode'           => $request->has('maintenance_mode'),
            ];

            $version = AppVersion::create([
                'version_name'          => $request->input('version_name'),
                'version_code'          => (int) $request->input('version_code'),
                'min_supported_version' => $request->input('min_supported_version') ?: $request->input('version_name'),
                'force_update'          => $request->has('force_update'),
                'title'                 => $request->input('title'),
                'changelog'             => $request->input('changelog'),
                'download_url'          => $downloadUrl,
                'file_size'             => $request->input('file_size') ?: '25 MB',
                'platform'              => $request->input('platform', 'android'),
                'is_active'             => true,
                'remote_flags'          => $remoteFlags,
                'release_date'          => now(),
            ]);

            // Sync with global app_version setting
            AppSetting::set('app_version', $version->version_name, 'general', 'Latest App Version');

            // Send Push Broadcast to users if checked
            if ($request->has('broadcast_push')) {
                PushNotificationService::broadcastAppUpdate(
                    versionName: $version->version_name,
                    title: $version->title,
                    changelog: $version->changelog ?: 'New updates and features available!',
                    downloadUrl: $version->download_url,
                    force: $version->force_update
                );
            }

            return back()->with('success', "Version {$version->version_name} published successfully! In-app update broadcasted to all devices.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to publish version: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Send instant Push Notification Broadcast to All Users from Admin Panel.
     */
    public function sendPushBroadcast(Request $request)
    {
        try {
            $request->validate([
                'push_title' => 'required|string|max:150',
                'push_body'  => 'required|string|max:500',
            ]);

            $title = $request->input('push_title');
            $body = $request->input('push_body');

            $allTokens = DeviceRegistration::where('is_active', true)
                ->pluck('fcm_token')
                ->merge(User::whereNotNull('fcm_token')->pluck('fcm_token'))
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (empty($allTokens)) {
                return back()->with('warning', 'No active device push tokens found in system yet.');
            }

            $res = PushNotificationService::sendToTokens(
                tokens: $allTokens,
                title: $title,
                body: $body,
                data: [
                    'action' => 'BROADCAST_MESSAGE',
                    'type'   => 'broadcast',
                    'title'  => $title,
                    'body'   => $body,
                ],
                priority: 'high'
            );

            return back()->with('success', 'Push notification broadcast dispatched to ' . count($allTokens) . ' device(s)!');
        } catch (\Exception $e) {
            return back()->with('error', 'Push broadcast failed: ' . $e->getMessage());
        }
    }
}
