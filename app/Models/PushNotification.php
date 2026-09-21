<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotification extends Model
{
    use HasFactory;

    protected $table = 'push_notifications';

    protected $fillable = [
        'firebase_app_id',
        'platform',
        'title',
        'message',
        'image_url',
        'action_url',
        'send_to',
        'target_user_ids',
        'sent_count',
        'failed_count',
        'total_target',
        'status',
        'response_payload',
        'created_by',
    ];

    protected $casts = [
        'target_user_ids' => 'array',
        'sent_count'      => 'integer',
        'failed_count'    => 'integer',
        'total_target'    => 'integer',
    ];

    /**
     * Associated Firebase App.
     */
    public function firebaseApp(): BelongsTo
    {
        return $this->belongsTo(FirebaseApp::class, 'firebase_app_id');
    }

    /**
     * Admin/User who sent this notification.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate success rate percentage.
     */
    public function getSuccessRateAttribute(): int
    {
        $total = $this->sent_count + $this->failed_count;
        if ($total <= 0) {
            return $this->sent_count > 0 ? 100 : 0;
        }
        return (int) round(($this->sent_count / $total) * 100);
    }
}
