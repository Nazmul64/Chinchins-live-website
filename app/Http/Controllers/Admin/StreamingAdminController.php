<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StreamingSetting;
use Illuminate\Http\Request;

class StreamingAdminController extends Controller
{
    /**
     * Display Streaming & Engine Settings.
     */
    public function index()
    {
        $setting = StreamingSetting::getSettings();
        return redirect()->route('admin.settings.index', ['tab' => 'streaming']);
    }

    /**
     * Update Streaming Engine Configurations.
     */
    public function update(Request $request)
    {
        $request->validate([
            'active_driver'         => 'required|in:vps_webrtc,agora,webrtc',
            'agora_project_name'    => 'nullable|string|max:150',
            'agora_app_id'          => 'nullable|string|max:200',
            'agora_app_certificate' => 'nullable|string|max:500',
            'agora_temp_token'      => 'nullable|string',
            'agora_manual_channel'  => 'nullable|string|max:150',
            'reverb_host'           => 'nullable|string|max:150',
            'reverb_port'           => 'nullable|integer',
            'token_expire_seconds'  => 'nullable|integer|min:300|max:604800',
        ]);

        $driver = $request->input('active_driver') === 'webrtc' ? 'vps_webrtc' : $request->input('active_driver');

        $setting = StreamingSetting::first();
        if (!$setting) {
            $setting = new StreamingSetting();
        }

        $setting->active_driver         = $driver;
        $setting->agora_project_name    = $request->input('agora_project_name');
        $setting->agora_app_id          = trim($request->input('agora_app_id') ?: '');
        $setting->agora_app_certificate = trim($request->input('agora_app_certificate') ?: '');
        $setting->agora_temp_token      = $request->input('agora_temp_token') ? trim($request->input('agora_temp_token')) : null;
        $setting->agora_manual_channel  = $request->input('agora_manual_channel') ? trim($request->input('agora_manual_channel')) : null;
        $setting->enable_video_call     = $request->boolean('enable_video_call', true);
        $setting->enable_audio_call     = $request->boolean('enable_audio_call', true);
        $setting->enable_live_stream    = $request->boolean('enable_live_stream', true);
        $setting->reverb_host           = $request->input('reverb_host');
        $setting->reverb_port           = $request->input('reverb_port') ? (int) $request->input('reverb_port') : null;
        $setting->token_expire_seconds  = (int) ($request->input('token_expire_seconds') ?: 86400);
        $setting->save();

        StreamingSetting::clearCache();

        $engineName = $driver === 'agora' ? 'Agora Cloud Engine (RTC/RTM)' : 'Hostinger VPS (Laravel Reverb + WebRTC)';
        $tokenMode = (!empty($setting->agora_temp_token)) ? ' [Admin Temp-Token Override Active]' : ' [Auto-Dynamic Token Active]';

        return back()->with('success', "Active Video/Audio Calling Engine updated: {$engineName}{$tokenMode}!")
                     ->with('active_tab', 'streaming');
    }
}
