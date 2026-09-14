<?php

namespace App\Services;

use App\Events\LiveGiftSentEvent;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\LiveRoom;
use App\Models\LiveStream;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GiftService
{
    /**
     * Process FinTech-grade virtual gift transaction with pessimistic row lock.
     */
    public function processGift(User $sender, array $data): GiftTransaction
    {
        return DB::transaction(function () use ($sender, $data) {
            // 1. Idempotency Gate
            if (!empty($data['idempotency_key'])) {
                $existingTx = GiftTransaction::where('idempotency_key', $data['idempotency_key'])->first();
                if ($existingTx) {
                    return $existingTx;
                }
            }

            $quantity = max(1, (int) ($data['quantity'] ?? 1));
            $gift = Gift::where('is_active', true)->findOrFail($data['gift_id']);
            $pricePerUnit = (int) ($gift->coin_price ?? $gift->coins ?? 100);
            $totalCost = $pricePerUnit * $quantity;

            // 2. Pessimistic Row Lock on Sender's Wallet
            $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->first();
            if (!$senderWallet) {
                $senderWallet = Wallet::create([
                    'user_id'  => $sender->id,
                    'balance'  => (int) ($sender->coins ?? 0),
                    'earnings' => 0,
                ]);
                $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->first();
            }

            if ($senderWallet->balance < $totalCost) {
                throw ValidationException::withMessages([
                    'balance' => ['Insufficient coin balance for this transaction. Required: ' . $totalCost . ', Available: ' . $senderWallet->balance]
                ]);
            }

            // 3. Atomically update balances (50/50 split to host earnings/balance)
            $hostEarned = (int) round($totalCost * 0.50);
            $senderWallet->decrement('balance', $totalCost);
            $sender->decrement('coins', $totalCost);

            $receiverWallet = Wallet::firstOrCreate(
                ['user_id' => $data['receiver_id']],
                ['balance' => 0, 'earnings' => 0]
            );
            $receiverWallet = Wallet::where('user_id', $data['receiver_id'])->lockForUpdate()->first();
            $receiverWallet->increment('balance', $hostEarned);
            $receiverWallet->increment('earnings', $hostEarned);

            User::where('id', $data['receiver_id'])->increment('coins', $hostEarned);

            // 4. Update Live Stream / Room Metrics if associated
            $roomId = $data['live_room_id'] ?? $data['stream_id'] ?? null;
            if (!empty($roomId)) {
                $liveRoom = LiveRoom::where('id', $roomId)->orWhere('channel_name', $roomId)->first();
                if ($liveRoom) {
                    $liveRoom->increment('total_diamonds_earned', $totalCost);
                }
                $liveStream = LiveStream::where('id', $roomId)->orWhere('channel_name', $roomId)->first();
                if ($liveStream) {
                    $liveStream->increment('total_diamonds_earned', $totalCost);
                }
            }

            // 5. Store Transaction Log
            $transaction = GiftTransaction::create([
                'idempotency_key' => $data['idempotency_key'] ?? ('tx_' . time() . '_' . rand(1000, 9999)),
                'sender_id'       => $sender->id,
                'receiver_id'     => $data['receiver_id'],
                'live_room_id'    => $roomId,
                'stream_id'       => (string) $roomId,
                'gift_id'         => $gift->id,
                'quantity'        => $quantity,
                'total_coins'     => $totalCost,
                'coins_spent'     => $totalCost,
                'status'          => 'completed',
            ]);

            // 6. Broadcast Real-Time Animation Payload
            $broadcastPayload = [
                'transaction_id'      => $transaction->id,
                'live_room_id'        => $roomId,
                'stream_id'           => $roomId,
                'sender_id'           => $sender->id,
                'sender_name'         => $sender->display_name ?? $sender->name,
                'sender_avatar'       => $sender->avatar_url,
                'gift_id'             => $gift->id,
                'gift_name'           => $gift->name,
                'gift_icon'           => $gift->icon_url,
                'animation_asset_url' => $gift->animation_asset_url,
                'animation_type'      => $gift->animation_type ?? 'svg',
                'quantity'            => $quantity,
                'total_coins'         => $totalCost,
                'created_at'          => now()->toIso8601String(),
            ];

            if (!empty($roomId)) {
                try {
                    broadcast(new LiveGiftSentEvent($roomId, $broadcastPayload))->toOthers();
                } catch (\Throwable $e) {}
            }

            return $transaction;
        });
    }
}
