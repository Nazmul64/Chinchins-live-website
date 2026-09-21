<?php

namespace App\Jobs;

use App\Models\CoinTransaction;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserGift;
use App\Models\Wallet;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGiftHistoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $senderId;
    public int $receiverId;
    public int $giftId;
    public int $giftCount;
    public int $totalCoins;
    public ?string $streamId;
    public ?string $context;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $senderId,
        int $receiverId,
        int $giftId,
        int $giftCount,
        int $totalCoins,
        ?string $streamId = null,
        ?string $context = 'live_stream'
    ) {
        $this->senderId   = $senderId;
        $this->receiverId = $receiverId;
        $this->giftId     = $giftId;
        $this->giftCount  = max(1, $giftCount);
        $this->totalCoins = $totalCoins;
        $this->streamId   = $streamId ?: ('stream_' . $receiverId);
        $this->context    = $context ?: 'live_stream';
    }

    /**
     * Execute the job in background queue worker.
     */
    public function handle(): void
    {
        try {
            $sender = User::find($this->senderId);
            $receiver = User::find($this->receiverId);
            $gift = Gift::find($this->giftId);

            if (!$sender || !$receiver || !$gift) {
                return;
            }

            // 1. Record in Gift Transactions table
            $giftTx = GiftTransaction::create([
                'stream_id'   => $this->streamId,
                'sender_id'   => $this->senderId,
                'receiver_id' => $this->receiverId,
                'gift_id'     => $this->giftId,
                'coins_spent' => $this->totalCoins,
            ]);

            // 2. Record in User Gifts collection ledger
            UserGift::create([
                'user_id'        => $this->receiverId,
                'sender_id'      => $this->senderId,
                'gift_id'        => $this->giftId,
                'quantity'       => $this->giftCount,
                'coins_per_unit' => (int) ($this->totalCoins / $this->giftCount),
                'total_coins'    => $this->totalCoins,
                'context'        => $this->context,
            ]);

            // 3. Update / Sync Wallets in background
            $senderWallet = Wallet::firstOrCreate(['user_id' => $this->senderId]);
            $receiverWallet = Wallet::firstOrCreate(['user_id' => $this->receiverId]);
            $receiverWallet->increment('earnings', $this->totalCoins);

            // 4. Coin Transactions Ledger
            CoinTransaction::create([
                'user_id'       => $this->senderId,
                'type'          => 'gift_sent',
                'amount'        => -$this->totalCoins,
                'balance_after' => (int) $sender->coins,
                'description'   => "Sent {$this->giftCount}x {$gift->name} to {$receiver->display_name}",
                'reference_id'  => $giftTx->id,
            ]);

            CoinTransaction::create([
                'user_id'       => $this->receiverId,
                'type'          => 'gift_received',
                'amount'        => $this->totalCoins,
                'balance_after' => (int) $receiverWallet->earnings,
                'description'   => "Received {$this->giftCount}x {$gift->name} from {$sender->display_name} (+{$this->totalCoins} earnings)",
                'reference_id'  => $giftTx->id,
            ]);

            // 5. In-App Notification
            Notification::createNotification(
                userId: $this->receiverId,
                actorId: $this->senderId,
                type: 'gift',
                title: "New Gift Received! 🎁",
                message: "{$sender->display_name} sent you {$this->giftCount}x {$gift->name} (+{$this->totalCoins} coins)!",
                data: [
                    'gift_id'      => $this->giftId,
                    'gift_name'    => $gift->name,
                    'gift_icon'    => $gift->image_url ?? $gift->icon_url,
                    'quantity'     => $this->giftCount,
                    'coins_earned' => $this->totalCoins,
                    'sender_id'    => $this->senderId,
                    'sender_name'  => $sender->display_name,
                ]
            );

            // 6. Fast FCM Push Notification
            PushNotificationService::sendGiftPush($sender, $receiver, $gift, $this->totalCoins);
        } catch (\Throwable $e) {
            Log::error('ProcessGiftHistoryJob failed: ' . $e->getMessage());
        }
    }
}
