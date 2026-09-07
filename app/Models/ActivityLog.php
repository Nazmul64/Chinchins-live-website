<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'module',
        'action',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a sensitive system activity in the audit trail.
     */
    public static function record(
        string $module,
        string $action,
        string $description,
        mixed $newDataOrUser = null,
        ?array $oldData = null,
        ?User $user = null
    ): ?self {
        try {
            $actor = null;
            $newData = null;

            if ($newDataOrUser instanceof User) {
                $actor = $newDataOrUser;
            } elseif (is_array($newDataOrUser)) {
                $newData = $newDataOrUser;
            }

            if (!$actor) {
                $actor = $user ?: Auth::user();
            }

            return static::create([
                'user_id'     => $actor?->id,
                'user_name'   => $actor?->name ?? ($actor?->email ?? 'System / Guest'),
                'user_role'   => $actor?->role?->name ?? ($actor?->isSuperAdmin() ? 'Super Admin' : 'Admin'),
                'module'      => strtolower($module),
                'action'      => strtolower($action),
                'description' => $description,
                'old_data'    => $oldData,
                'new_data'    => $newData,
                'ip_address'  => Request::ip() ?? '127.0.0.1',
                'user_agent'  => Request::userAgent() ?? 'Web',
            ]);
        } catch (\Throwable $e) {
            // Silently fallback if table doesn't exist yet
            return null;
        }
    }
}
