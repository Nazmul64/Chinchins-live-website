<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    use HasFactory;

    protected $table = 'user_reports';

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reason_type',
        'reason_title',
        'description',
        'proof_image',
        'status',
        'admin_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public static array $reasonTypes = [
        'child_abuse' => 'Child sexual abuse and exploitation',
        'unreasonable_demands' => 'Unreasonable demands / Harassment',
        'sexual_content' => 'Adult / Sexual related content',
        'harassment' => 'Abuse, threat, or hate speech',
        'fraud_scam' => 'Fraud, financial scam, or fake profile',
        'other' => 'Other rule violations',
    ];

    /**
     * User who filed report.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * User who is reported.
     */
    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Full URL for proof image
     */
    public function getProofImageFullUrlAttribute(): ?string
    {
        if (empty($this->proof_image)) {
            return null;
        }
        if (str_starts_with($this->proof_image, 'http://') || str_starts_with($this->proof_image, 'https://')) {
            return $this->proof_image;
        }
        return asset(ltrim($this->proof_image, '/'));
    }
}
