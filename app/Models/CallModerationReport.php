<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallModerationReport extends Model
{
    use HasFactory;

    protected $table = 'call_moderation_reports';

    protected $fillable = [
        'call_session_id',
        'reporter_id',
        'reported_user_id',
        'reason',
        'description',
        'evidence_snapshots',
        'evidence_video_url',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'evidence_snapshots' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
