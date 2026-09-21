<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceRegistration;
use App\Models\FirebaseApp;
use App\Models\PushNotification;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FirebaseNotificationAdminController extends Controller
{
    /**
     * Display Firebase Apps Management Page (Screenshot 1).
     */
    public function apps()
    {
        try {
            $apps = FirebaseApp::withCount(['deviceRegistrations', 'pushNotifications'])->latest()->get();
        } catch (\Throwable $e) {
            $apps = collect();
        }
        return view('admin.firebase.apps', compact('apps'));
    }

    /**
     * Store a new Firebase App with JSON service account file upload.
     */
    public function storeApp(Request $request)
    {
        $request->validate([
            'app_name'             => 'required|string|max:150',
            'package_name'         => 'required|string|max:200|unique:firebase_apps,package_name',
            'firebase_json_file'   => 'nullable|file|mimes:json,txt|max:2048',
            'server_key'           => 'nullable|string',
        ]);

        $serviceAccountJson = null;
        $serviceAccountPath = null;
        $projectId = null;
        $clientEmail = null;

        if ($request->hasFile('firebase_json_file')) {
            $file = $request->file('firebase_json_file');
            $content = file_get_contents($file->getRealPath());
            $json = json_decode($content, true);

            if ($json && is_array($json)) {
                $serviceAccountJson = $content;
                $projectId = $json['project_id'] ?? null;
                $clientEmail = $json['client_email'] ?? null;

                $filename = 'firebase_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->package_name) . '.json';
                $serviceAccountPath = $file->storeAs('firebase', $filename);
            }
        }

        FirebaseApp::create([
            'app_name'             => $request->input('app_name'),
            'package_name'         => trim($request->input('package_name')),
            'service_account_json' => $serviceAccountJson,
            'service_account_path' => $serviceAccountPath,
            'server_key'           => $request->input('server_key'),
            'project_id'           => $projectId,
            'client_email'         => $clientEmail,
            'is_active'            => true,
        ]);

        return back()->with('success', 'Firebase App configured and saved successfully!');
    }

    /**
     * Update an existing Firebase App.
     */
    public function updateApp(Request $request, $id)
    {
        $app = FirebaseApp::findOrFail($id);

        $request->validate([
            'app_name'             => 'required|string|max:150',
            'package_name'         => 'required|string|max:200|unique:firebase_apps,package_name,' . $id,
            'firebase_json_file'   => 'nullable|file|mimes:json,txt|max:2048',
            'server_key'           => 'nullable|string',
        ]);

        $data = [
            'app_name'     => $request->input('app_name'),
            'package_name' => trim($request->input('package_name')),
            'server_key'   => $request->input('server_key'),
        ];

        if ($request->hasFile('firebase_json_file')) {
            $file = $request->file('firebase_json_file');
            $content = file_get_contents($file->getRealPath());
            $json = json_decode($content, true);

            if ($json && is_array($json)) {
                $data['service_account_json'] = $content;
                $data['project_id'] = $json['project_id'] ?? null;
                $data['client_email'] = $json['client_email'] ?? null;

                $filename = 'firebase_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->package_name) . '.json';
                $data['service_account_path'] = $file->storeAs('firebase', $filename);
            }
        }

        $app->update($data);

        return back()->with('success', 'Firebase App updated successfully!');
    }

    /**
     * Delete a Firebase App.
     */
    public function destroyApp($id)
    {
        $app = FirebaseApp::findOrFail($id);
        $app->delete();

        return back()->with('success', 'Firebase App removed successfully.');
    }

    /**
     * Toggle Firebase App active status.
     */
    public function toggleAppStatus($id)
    {
        $app = FirebaseApp::findOrFail($id);
        $app->is_active = !$app->is_active;
        $app->save();

        return back()->with('success', "App status changed to " . ($app->is_active ? 'Active' : 'Inactive'));
    }

    /**
     * Display Send Push Notification Screen (Screenshots 2 & 3).
     */
    public function send()
    {
        try {
            $apps = FirebaseApp::where('is_active', true)->get();
            $recentNotifications = PushNotification::with('firebaseApp')->latest()->take(10)->get();

            // Platform stats pill counts
            $firebaseCount = PushNotification::where('platform', 'firebase')->count();
            $oneSignalCount = PushNotification::where('platform', 'onesignal')->count();
            $totalCount = PushNotification::count();

            $users = User::select('id', 'name', 'email', 'avatar', 'fcm_token', 'account_id')
                ->where(function($q) {
                    $q->whereNotNull('fcm_token')->where('fcm_token', '!=', '');
                })
                ->orWhereHas('deviceRegistrations')
                ->take(50)
                ->get();
        } catch (\Throwable $e) {
            $apps = collect();
            $recentNotifications = collect();
            $firebaseCount = 0;
            $oneSignalCount = 0;
            $totalCount = 0;
            $users = collect();
        }

        return view('admin.firebase.send', compact('apps', 'recentNotifications', 'firebaseCount', 'oneSignalCount', 'totalCount', 'users'));
    }

    /**
     * Process & Send Push Notification from Admin Form.
     */
    public function submitSend(Request $request)
    {
        $request->validate([
            'platform'        => 'required|in:firebase,onesignal,both',
            'firebase_app_id' => 'nullable|exists:firebase_apps,id',
            'title'           => 'required|string|max:255',
            'message'         => 'required|string|max:1000',
            'image_url'       => 'nullable|string|max:500',
            'action_url'      => 'nullable|string|max:255',
            'send_to'         => 'required|in:all,specific',
            'user_ids'        => 'nullable|array',
        ]);

        $platform = $request->input('platform');
        $firebaseAppId = $request->input('firebase_app_id');
        $title = $request->input('title');
        $message = $request->input('message');
        $imageUrl = $request->input('image_url');
        $actionUrl = $request->input('action_url');
        $sendTo = $request->input('send_to');
        $targetUserIds = $sendTo === 'all' ? ['all'] : ($request->input('user_ids') ?: []);

        $result = PushNotificationService::sendPush(
            platform: $platform,
            targetUserIds: $targetUserIds,
            title: $title,
            body: $message,
            imageUrl: $imageUrl,
            actionUrl: $actionUrl,
            firebaseAppId: $firebaseAppId ? (int) $firebaseAppId : null
        );

        $sent = $result['sent_count'] ?? 0;
        $failed = $result['failed_count'] ?? 0;

        return back()->with('success', "Push Notification processed! Sent: {$sent}, Failed: {$failed}");
    }

    /**
     * Display Push Notification History / Reports (Screenshot 4).
     */
    public function history(Request $request)
    {
        try {
            $apps = FirebaseApp::all();

            $query = PushNotification::with(['firebaseApp', 'creator'])->latest();

            if ($request->filled('app_id')) {
                $query->where('firebase_app_id', $request->app_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $notifications = $query->paginate(15)->withQueryString();

            // Stats calculation
            $totalNotifications = PushNotification::count();
            $totalSent = PushNotification::sum('sent_count');
            $totalFailed = PushNotification::sum('failed_count');
            $totalDispatches = $totalSent + $totalFailed;
            $avgSuccessRate = $totalDispatches > 0 ? (int) round(($totalSent / $totalDispatches) * 100) : 0;
        } catch (\Throwable $e) {
            $apps = collect();
            $notifications = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $totalNotifications = 0;
            $totalSent = 0;
            $totalFailed = 0;
            $avgSuccessRate = 0;
        }

        return view('admin.firebase.history', compact(
            'apps',
            'notifications',
            'totalNotifications',
            'totalSent',
            'totalFailed',
            'avgSuccessRate'
        ));
    }

    /**
     * View specific notification details (JSON for modal / AJAX).
     */
    public function viewNotification($id)
    {
        $notification = PushNotification::with(['firebaseApp', 'creator'])->findOrFail($id);
        return response()->json([
            'status' => true,
            'data'   => $notification,
        ]);
    }

    /**
     * Display Users with FCM Tokens (Screenshot 5).
     */
    public function users(Request $request)
    {
        try {
            $apps = FirebaseApp::all();

            $query = DeviceRegistration::with(['user', 'firebaseApp'])->latest('last_active_at');

            if ($request->filled('app_id')) {
                $query->where('firebase_app_id', $request->app_id);
            }

            if ($request->filled('device_type') && $request->device_type !== 'all') {
                $query->where('device_type', strtolower($request->device_type));
            }

            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('device_id', 'like', "%{$s}%")
                      ->orWhere('fcm_token', 'like', "%{$s}%")
                      ->orWhereHas('user', function ($uq) use ($s) {
                          $uq->where('name', 'like', "%{$s}%")
                             ->orWhere('email', 'like', "%{$s}%")
                             ->orWhere('account_id', 'like', "%{$s}%");
                      });
                });
            }

            $devices = $query->paginate(20)->withQueryString();

            // Top Stat cards
            $totalUsersWithFcm = DeviceRegistration::whereNotNull('fcm_token')
                ->distinct('user_id')
                ->count('user_id');
            if ($totalUsersWithFcm == 0) {
                $totalUsersWithFcm = User::whereNotNull('fcm_token')->count();
            }

            $activeTokens = DeviceRegistration::where('is_active', true)->whereNotNull('fcm_token')->count();
            $androidUsers = DeviceRegistration::where('device_type', 'android')->where('is_active', true)->count();
            $iosUsers = DeviceRegistration::where('device_type', 'ios')->where('is_active', true)->count();
        } catch (\Throwable $e) {
            $apps = collect();
            $devices = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $totalUsersWithFcm = 0;
            $activeTokens = 0;
            $androidUsers = 0;
            $iosUsers = 0;
        }

        return view('admin.firebase.users', compact(
            'apps',
            'devices',
            'totalUsersWithFcm',
            'activeTokens',
            'androidUsers',
            'iosUsers'
        ));
    }

    /**
     * Send direct push notification to an individual device/user.
     */
    public function sendToUser(Request $request, $userId)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        $user = User::findOrFail($userId);
        $tokens = PushNotificationService::getUserTokens($user->id);

        if (empty($tokens)) {
            return back()->with('error', 'No active FCM tokens found for this user.');
        }

        $res = PushNotificationService::sendPush(
            platform: 'firebase',
            targetUserIds: [$user->id],
            title: $request->title,
            body: $request->message,
            imageUrl: $request->image_url,
            actionUrl: $request->action_url
        );

        return back()->with('success', "Notification dispatched to {$user->name}! Sent: " . ($res['sent_count'] ?? 1));
    }
}
