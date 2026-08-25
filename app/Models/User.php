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
        'level',
        'country',
        'city',
        'gender',
        'age',
        'introduction',
        'languages',
        'tags',
        'video_call_rate',
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
    ];

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
            'age'                 => 'integer',
            'video_call_rate'     => 'integer',
            'close_friends_count' => 'integer',
        ];
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
     * Accessor for full Avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            // Default avatar if none uploaded
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        return asset('storage/' . ltrim($this->avatar, '/'));
    }

    /**
     * Accessor for full Cover Photo URL.
     * Uses explicit cover_photo or falls back to first gallery image.
     */
    public function getCoverPhotoUrlAttribute(): ?string
    {
        $cover = $this->cover_photo;

        if (empty($cover) && !empty($this->gallery_images) && is_array($this->gallery_images) && count($this->gallery_images) > 0) {
            $cover = $this->gallery_images[0];
        }

        if (empty($cover)) {
            return null;
        }

        if (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://')) {
            return $cover;
        }

        return asset('storage/' . ltrim($cover, '/'));
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

        return array_map(function ($img) {
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                return $img;
            }
            return asset('storage/' . ltrim($img, '/'));
        }, $images);
    }

    /**
     * Accessor for display name (Nickname preferred, then Name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->nickname ?: ($this->name ?: trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')));
    }
}
