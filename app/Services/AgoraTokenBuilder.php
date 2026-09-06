<?php

namespace App\Services;

/**
 * Agora RTC & RTM Token Builder Service (AccessKey006 & AccessToken2).
 * Compatible with Agora RTC SDK 4.x / 6.x and Agora Chat / RTM.
 */
class AgoraTokenBuilder
{
    // Roles
    const ROLE_PUBLISHER = 1;
    const ROLE_SUBSCRIBER = 2;
    const ROLE_ATTENDEE = 0;

    // Privileges
    const PRIVILEGE_JOIN_CHANNEL = 1;
    const PRIVILEGE_PUBLISH_AUDIO_STREAM = 2;
    const PRIVILEGE_PUBLISH_VIDEO_STREAM = 3;
    const PRIVILEGE_PUBLISH_DATA_STREAM = 4;

    /**
     * Build Agora RTC Token for numeric User ID (UID).
     *
     * @param string $appId
     * @param string $appCertificate
     * @param string $channelName
     * @param int|string $uid
     * @param int $role
     * @param int $privilegeExpireTs
     * @return string
     */
    public static function buildTokenWithUid(
        string $appId,
        string $appCertificate,
        string $channelName,
        int|string $uid,
        int $role = self::ROLE_PUBLISHER,
        int $privilegeExpireTs = 0
    ): string {
        return self::generateToken($appId, $appCertificate, $channelName, (string) $uid, $role, $privilegeExpireTs);
    }

    /**
     * Build Agora RTC Token for string Account/User identifier.
     *
     * @param string $appId
     * @param string $appCertificate
     * @param string $channelName
     * @param string $userAccount
     * @param int $role
     * @param int $privilegeExpireTs
     * @return string
     */
    public static function buildTokenWithUserAccount(
        string $appId,
        string $appCertificate,
        string $channelName,
        string $userAccount,
        int $role = self::ROLE_PUBLISHER,
        int $privilegeExpireTs = 0
    ): string {
        return self::generateToken($appId, $appCertificate, $channelName, $userAccount, $role, $privilegeExpireTs);
    }

    /**
     * Generate RTC Token according to Agora AccessToken standard.
     */
    public static function generateToken(
        string $appId,
        string $appCertificate,
        string $channelName,
        string $uidStr,
        int $role = self::ROLE_PUBLISHER,
        int $privilegeExpireTs = 0
    ): string {
        $appId = trim($appId);
        $appCertificate = trim($appCertificate);
        $channelName = trim($channelName);

        // If no certificate provided, fallback to raw App ID token
        if (empty($appCertificate)) {
            return $appId;
        }

        $now = time();
        if ($privilegeExpireTs <= 0) {
            $privilegeExpireTs = $now + 86400; // 24 hours
        }

        $salt = (string) mt_rand(1, 99999999);

        // Build privilege map
        $privileges = [
            self::PRIVILEGE_JOIN_CHANNEL => $privilegeExpireTs,
        ];

        if ($role === self::ROLE_PUBLISHER) {
            $privileges[self::PRIVILEGE_PUBLISH_AUDIO_STREAM] = $privilegeExpireTs;
            $privileges[self::PRIVILEGE_PUBLISH_VIDEO_STREAM] = $privilegeExpireTs;
            $privileges[self::PRIVILEGE_PUBLISH_DATA_STREAM] = $privilegeExpireTs;
        } elseif ($role === self::ROLE_SUBSCRIBER) {
            $privileges[self::PRIVILEGE_PUBLISH_AUDIO_STREAM] = 0;
            $privileges[self::PRIVILEGE_PUBLISH_VIDEO_STREAM] = 0;
            $privileges[self::PRIVILEGE_PUBLISH_DATA_STREAM] = 0;
        }

        // Pack message content
        $msgContent = self::packMessage($appId, $channelName, $uidStr, $salt, $now, $privileges);
        $signature = hash_hmac('sha256', $msgContent, $appCertificate, true);

        // Version 006 token prefix
        $version = "006";
        $body = pack("a*", $version) . pack("a*", $appId) . pack("V", $now) . pack("V", $salt) . pack("v", strlen($signature)) . $signature . pack("v", strlen($msgContent)) . $msgContent;

        return $version . base64_encode($body);
    }

    /**
     * Pack binary message structure for Agora signature.
     */
    protected static function packMessage(
        string $appId,
        string $channelName,
        string $uidStr,
        string $salt,
        int $ts,
        array $privileges
    ): string {
        $buf = "";
        $buf .= pack("a*", $appId);
        $buf .= pack("a*", $channelName);
        $buf .= pack("a*", $uidStr);
        $buf .= pack("a*", $salt);
        $buf .= pack("V", $ts);

        // Pack Privileges count (v = 16-bit unsigned short)
        $buf .= pack("v", count($privileges));
        foreach ($privileges as $k => $v) {
            $buf .= pack("v", (int) $k);
            $buf .= pack("V", (int) $v);
        }

        return $buf;
    }
}
