<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'account_id',
        'first_name',
        'last_name',
        'name',
        'nickname',
        'phone',
        'email',
        'password',
        'avatar',
        'cover_photo',
        'gallery_images',
        'is_verified',
        'is_active',
        'last_seen_at',
        'online_status',
        'fcm_token',
        'device_token',
        'device_type',
        'is_locked',
        'locked_reason',
        'locked_at',
        'unlocked_at',
        'level',
        'country',
        'city',
        'gender',
        'age',
        'introduction',
        'languages',
        'tags',
        'video_call_rate',
        'coins',
        'free_calls_used',
        'free_messages_used',
        'free_messages_limit',
        'is_busy',
        'auto_call_enabled',
        'close_friends_count',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'avatar_url',
        'cover_photo_url',
        'gallery_image_urls',
        'display_name',
        'is_online',
        'status_text',
        'profile_picture',
        'photos',
        'gallery',
        'kyc_status',
    ];

    /**
     * User's presence and heartbeat session.
     */
    public function presence()
    {
        return $this->hasOne(UserPresence::class);
    }

    /**
     * User's KYC identity verification record.
     */
    public function kycVerification()
    {
        return $this->hasOne(KycVerification::class)->latestOfMany();
    }

    /**
     * User's KYC verification history.
     */
    public function kycVerifications()
    {
        return $this->hasMany(KycVerification::class)->latest();
    }

    /**
     * Accessor for user's KYC verification status.
     * Values: 'approved' | 'pending' | 'rejected' | 'not_submitted'
     */
    public function getKycStatusAttribute(): string
    {
        if ($this->relationLoaded('kycVerification')) {
            return $this->kycVerification?->status ?? ($this->is_verified ? 'approved' : 'not_submitted');
        }

        $latestKyc = $this->kycVerification()->first();
        if ($latestKyc) {
            return $latestKyc->status;
        }

        return $this->is_verified ? 'approved' : 'not_submitted';
    }

    /**
     * Accessor for online status boolean.
     * True only if user is active, not locked, and recently active (within last 5 minutes) or marked online.
     */
    public function getIsOnlineAttribute(): bool
    {
        if ($this->is_locked || !$this->is_active) {
            return false;
        }

        if (in_array($this->online_status, ['offline', 'inactive'])) {
            return false;
        }

        if ($this->last_seen_at) {
            return $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(5));
        }

        if (in_array($this->online_status, ['online', 'busy', 'in_call'])) {
            return true;
        }

        return false;
    }

    /**
     * Accessor for human-readable status text.
     * Returns: 'Online' | 'Inactive' | 'In Call' | 'Busy' | 'Locked'
     */
    public function getStatusTextAttribute(): string
    {
        if ($this->is_locked) {
            return 'Locked';
        }

        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->online_status === 'in_call') {
            return 'In Call';
        }

        if ($this->online_status === 'busy') {
            return 'Busy';
        }

        if ($this->is_online) {
            return 'Online';
        }

        return 'Inactive';
    }

    /**
     * Helper alias for profile_picture.
     */
    public function getProfilePictureAttribute(): ?string
    {
        return $this->avatar_url;
    }

    /**
     * Helper alias for photos.
     */
    public function getPhotosAttribute(): array
    {
        return $this->gallery_image_urls;
    }

    /**
     * Helper alias for gallery.
     */
    public function getGalleryAttribute(): array
    {
        return $this->gallery_image_urls;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'gallery_images'      => 'array',
            'languages'           => 'array',
            'tags'                => 'array',
            'is_verified'         => 'boolean',
            'is_active'           => 'boolean',
            'last_seen_at'        => 'datetime',
            'is_locked'           => 'boolean',
            'locked_at'           => 'datetime',
            'unlocked_at'         => 'datetime',
            'age'                 => 'integer',
            'video_call_rate'     => 'integer',
            'coins'               => 'integer',
            'free_calls_used'     => 'integer',
            'close_friends_count' => 'integer',
        ];
    }

    /**
     * Check if user is eligible for free trial call.
     */
    public function isEligibleForFreeCall(): bool
    {
        $config = CallSetting::getAllConfig();
        if (!$config['is_free_call_enabled']) {
            return false;
        }

        $limit = (int) $config['free_calls_per_user'];
        return ($this->free_calls_used ?: 0) < $limit;
    }

    /**
     * Mark a free trial call as used.
     */
    public function markFreeCallUsed(): void
    {
        $this->increment('free_calls_used');
        $this->refresh();
    }

    /**
     * User's deposit requests.
     */
    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }

    /**
     * User's withdrawal requests.
     */
    public function withdrawRequests()
    {
        return $this->hasMany(WithdrawRequest::class);
    }

    /**
     * User's coin transaction history.
     */
    public function coinTransactions()
    {
        return $this->hasMany(CoinTransaction::class)->latest();
    }

    /**
     * Add coins to user balance and log transaction.
     */
    public function addCoins(int $amount, string $type = 'admin_add', ?string $description = null, ?string $referenceId = null): self
    {
        $this->increment('coins', $amount);
        $this->refresh();

        $this->coinTransactions()->create([
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $this->coins,
            'description' => $description ?: "Added {$amount} coins",
            'reference_id' => $referenceId,
        ]);

        return $this;
    }

    /**
     * Deduct coins from user balance and log transaction.
     */
    public function deductCoins(int $amount, string $type = 'admin_deduct', ?string $description = null, ?string $referenceId = null): bool
    {
        if ($this->coins < $amount) {
            return false;
        }

        $this->decrement('coins', $amount);
        $this->refresh();

        $this->coinTransactions()->create([
            'type' => $type,
            'amount' => -$amount,
            'balance_after' => $this->coins,
            'description' => $description ?: "Deducted {$amount} coins",
            'reference_id' => $referenceId,
        ]);

        return true;
    }

    /**
     * Auto generate unique 10-12 digit Account ID on creation.
     */
    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->account_id)) {
                $user->account_id = static::generateUniqueAccountId();
            }

            if (empty($user->name)) {
                $user->name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            }

            if (empty($user->nickname)) {
                $user->nickname = $user->name ?: ('User' . substr($user->account_id, -4));
            }

            if ($user->gallery_images === null) {
                $user->gallery_images = [];
            }

            if ($user->languages === null) {
                $user->languages = [];
            }

            if ($user->tags === null) {
                $user->tags = [];
            }
        });
    }

    /**
     * Generate unique 10-12 digit numeric string for account_id.
     */
    public static function generateUniqueAccountId(): string
    {
        do {
            // Generate 10-digit random number (e.g. 6022816358 or 602281635)
            $accountId = (string) random_int(1000000000, 9999999999);
        } while (static::where('account_id', $accountId)->exists());

        return $accountId;
    }

    /**
     * Helper to resolve full URL for any image path.
     * Serves directly from public/uploads folder without requiring symlinks.
     */
    public static function resolveImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        if (str_starts_with($cleanPath, 'uploads/')) {
            return asset($cleanPath);
        }

        if (str_starts_with($cleanPath, 'profile/') || str_starts_with($cleanPath, 'profiles/') || str_starts_with($cleanPath, 'payment_gateways/')) {
            return asset('uploads/' . $cleanPath);
        }

        if (str_starts_with($cleanPath, 'storage/')) {
            $withoutStorage = substr($cleanPath, 8);
            return asset('uploads/' . $withoutStorage);
        }

        return asset($cleanPath);
    }

    /**
     * Accessor for full Avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!empty($this->avatar)) {
            return static::resolveImageUrl($this->avatar);
        }

        return null;
    }

    /**
     * Accessor for full Cover Photo URL.
     */
    public function getCoverPhotoUrlAttribute(): ?string
    {
        if (!empty($this->cover_photo)) {
            return static::resolveImageUrl($this->cover_photo);
        }

        return null;
    }

    /**
     * Accessor for full URLs of gallery images.
     */
    public function getGalleryImageUrlsAttribute(): array
    {
        $images = $this->gallery_images;
        if (!is_array($images)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($img) {
            return static::resolveImageUrl($img);
        }, $images)));
    }

    /**
     * Accessor for display name (Nickname preferred, then Name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->nickname ?: ($this->name ?: trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')));
    }

    /**
     * Gifts received by this user (as host / streamer / profile owner).
     */
    public function receivedGifts()
    {
        return $this->hasMany(UserGift::class, 'user_id');
    }

    /**
     * Gifts sent by this user to others.
     */
    public function sentGifts()
    {
        return $this->hasMany(UserGift::class, 'sender_id');
    }
}

