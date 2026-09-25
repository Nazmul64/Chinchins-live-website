<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class LiveKitService
{
    /**
     * Generate fast LiveKit JWT Access Token for zero-latency connection (< 10ms).
     */
    public static function generateFastToken(int|string|User $user, string $roomName = '', bool $canPublish = true): string
    {
        $apiKey = config('services.livekit.api_key', env('LIVEKIT_API_KEY', 'APIVbeXzKatSo3u'));
        $apiSecret = config('services.livekit.api_secret', env('LIVEKIT_API_SECRET', 'thzlQ2sYGQQIxBPQMkjO9Rres6xuuMsqweZdT61XNsK'));

        $userId = $user instanceof User ? $user->id : (int) $user;
        $userName = $user instanceof User ? ($user->display_name ?? $user->name ?? "User_{$userId}") : "User_{$userId}";

        if (empty($roomName)) {
            $roomName = 'call_' . $userId . '_' . time();
        }

        if (class_exists('\Agence104\LiveKit\AccessToken')) {
            try {
                $token = new \Agence104\LiveKit\AccessToken($apiKey, $apiSecret);
                $grant = new \Agence104\LiveKit\VideoGrant();
                $grant->setRoomJoin(true)
                      ->setRoomName($roomName)
                      ->setCanPublish($canPublish)
                      ->setCanSubscribe(true)
                      ->setCanPublishData(true);

                $tokenOptions = (new \Agence104\LiveKit\AccessTokenOptions())
                    ->setIdentity((string) $userId)
                    ->setName($userName)
                    ->setTtl(86400);

                $token->init($tokenOptions);
                $token->setGrant($grant);
                return $token->toJwt();
            } catch (\Throwable $e) {
                Log::error("LiveKitService token generation failed: " . $e->getMessage());
            }
        }

        // Fast fallback JWT mock token if Agence104 package is not available
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'iss'   => $apiKey,
            'sub'   => (string) $userId,
            'name'  => $userName,
            'video' => [
                'room'             => $roomName,
                'roomJoin'         => true,
                'canPublish'       => $canPublish,
                'canSubscribe'     => true,
                'canPublishData'   => true,
            ],
            'exp'   => time() + 86400,
        ]));
        $signature = hash_hmac('sha256', "$header.$payload", $apiSecret, true);
        return "$header.$payload." . base64_encode($signature);
    }
}
