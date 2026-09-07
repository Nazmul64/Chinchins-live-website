<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'email',
        'role_name',
        'login_at',
        'logout_at',
        'ip_address',
        'browser',
        'device',
        'status',
        'failure_reason',
    ];

    protected $casts = [
        'login_at'  => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a login attempt safely.
     */
    public static function recordAttempt(
        ?User $user,
        bool|string $isSuccessfulOrStatus = true,
        ?string $failureReason = null
    ): ?self {
        try {
            $userAgent = Request::userAgent() ?: '';
            
            $browser = 'Chrome / Web';
            if (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
            elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) $browser = 'Safari';
            elseif (str_contains($userAgent, 'Edg')) $browser = 'Edge';
            elseif (str_contains($userAgent, 'Dart') || str_contains($userAgent, 'okhttp')) $browser = 'Mobile App';

            $device = 'Desktop';
            if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone')) {
                $device = 'Mobile Device';
            }

            $status = is_bool($isSuccessfulOrStatus) ? ($isSuccessfulOrStatus ? 'success' : 'failed') : $isSuccessfulOrStatus;

            return static::create([
                'user_id'        => $user?->id,
                'user_name'      => $user?->name ?? ($user?->email ?? 'Guest'),
                'email'          => $user?->email ?? 'unknown',
                'role_name'      => $user?->role?->name ?? ($user?->isSuperAdmin() ? 'Super Admin' : 'Admin'),
                'login_at'       => now(),
                'ip_address'     => Request::ip() ?? '127.0.0.1',
                'browser'        => $browser,
                'device'         => $device,
                'status'         => $status,
                'failure_reason' => $failureReason,
            ]);
        } catch (\Throwable $e) {
            // Silently fallback if table doesn't exist yet
            return null;
        }
    }
}
