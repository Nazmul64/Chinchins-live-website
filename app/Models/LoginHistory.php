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
     * Record a login attempt.
     */
    public static function recordAttempt(
        ?User $user,
        string $emailOrPhone,
        string $status = 'success',
        ?string $failureReason = null
    ): self {
        $userAgent = Request::userAgent() ?: '';
        
        // Simple user-agent parser
        $browser = 'Unknown Browser';
        if (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) $browser = 'Chrome';
        elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) $browser = 'Safari';
        elseif (str_contains($userAgent, 'Edg')) $browser = 'Edge';
        elseif (str_contains($userAgent, 'Dart') || str_contains($userAgent, 'okhttp')) $browser = 'Mobile App / API';

        $device = 'Desktop';
        if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone')) {
            $device = 'Mobile Device';
        }

        return static::create([
            'user_id'        => $user?->id,
            'user_name'      => $user?->display_name ?: ($emailOrPhone),
            'email'          => $user?->email ?: $emailOrPhone,
            'role_name'      => $user?->role?->name ?: 'Staff/Admin',
            'login_at'       => now(),
            'ip_address'     => Request::ip(),
            'browser'        => $browser,
            'device'         => $device,
            'status'         => $status,
            'failure_reason' => $failureReason,
        ]);
    }
}
