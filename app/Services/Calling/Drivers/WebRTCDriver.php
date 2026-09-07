<?php

namespace App\Services\Calling\Drivers;

use App\Models\StreamingSetting;
use App\Models\User;
use App\Services\Calling\Contracts\CallingDriverInterface;

class WebRTCDriver implements CallingDriverInterface
{
    public function getName(): string
    {
        return 'vps_webrtc';
    }

    public function initializeSession(?User $user, string $channelName, string $callType = 'video', string $role = 'publisher', array $options = []): array
    {
        $setting = StreamingSetting::getSettings();
        $reverbConfig = $setting->getReverbConfig();
        $uid = (int) ($options['uid'] ?? ($user ? $user->id : mt_rand(100000, 999999)));

        return [
            'success'          => true,
            'status'           => true,
            'driver'           => 'vps_webrtc',
            'channel_name'     => $channelName,
            'user_id'          => $user?->id ?? $uid,
            'account_id'       => $user?->account_id,
            'uid'              => $uid,
            'target_user'      => $options['target_user'] ?? null,
            'call_type'        => $callType,
            'role'             => $role,
            'signaling_host'   => $reverbConfig['host'],
            'signaling_port'   => $reverbConfig['port'],
            'signaling_scheme' => $reverbConfig['scheme'],
            'app_key'          => $reverbConfig['app_key'],
            'auth_endpoint'    => $reverbConfig['auth_endpoint'],
            'enable_video'     => (bool) $setting->enable_video_call,
            'enable_audio'     => (bool) $setting->enable_audio_call,
            'enable_live'      => (bool) $setting->enable_live_stream,
            'status_text'      => 'Connecting via Reverb WebRTC...',
            'message'          => 'Ready',
        ];
    }

    public function refreshToken(string $channelName, int|string $uid, string $role = 'publisher'): array
    {
        return [
            'success' => true,
            'driver'  => 'vps_webrtc',
            'message' => 'WebRTC uses continuous WebSocket signaling, token refresh not required.',
        ];
    }
}
