<?php

namespace App\Services\Calling\Contracts;

use App\Models\User;

interface CallingDriverInterface
{
    /**
     * Get the unique name of the calling driver ('vps_webrtc' or 'agora').
     */
    public function getName(): string;

    /**
     * Initialize call session credentials for caller or receiver.
     *
     * @param User|null $user
     * @param string $channelName
     * @param string $callType ('video', 'audio', 'live')
     * @param string $role ('publisher', 'subscriber')
     * @param array $options Additional metadata (target_user, uid, etc.)
     * @return array
     */
    public function initializeSession(?User $user, string $channelName, string $callType = 'video', string $role = 'publisher', array $options = []): array;

    /**
     * Generate or refresh token for active call.
     *
     * @param string $channelName
     * @param int|string $uid
     * @param string $role
     * @return array
     */
    public function refreshToken(string $channelName, int|string $uid, string $role = 'publisher'): array;
}
