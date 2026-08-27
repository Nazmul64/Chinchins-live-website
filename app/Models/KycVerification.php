<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycVerification extends Model
{
    use HasFactory;

    protected $table = 'kyc_verifications';

    protected $fillable = [
        'user_id',
        'document_type',
        'full_name',
        'document_number',
        'date_of_birth',
        'front_image',
        'back_image',
        'selfie_image',
        'face_center_image',
        'face_left_image',
        'face_right_image',
        'face_blink_image',
        'liveness_data',
        'ai_detection_meta',
        'status',
        'user_notes',
        'rejection_reason',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
        'submitted_at',
    ];

    protected $casts = [
        'date_of_birth'     => 'date:Y-m-d',
        'liveness_data'     => 'array',
        'ai_detection_meta' => 'array',
        'reviewed_at'       => 'datetime',
        'submitted_at'      => 'datetime',
    ];

    protected $appends = [
        'front_image_url',
        'back_image_url',
        'selfie_image_url',
        'face_center_image_url',
        'face_left_image_url',
        'face_right_image_url',
        'face_blink_image_url',
        'document_type_label',
        'status_badge_class',
    ];

    /**
     * User who submitted the verification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin who reviewed/approved the verification.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Helper to resolve full URL for any KYC image path.
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

        return asset('uploads/' . $cleanPath);
    }

    /**
     * Full URL of document front image.
     */
    public function getFrontImageUrlAttribute(): ?string
    {
        return static::resolveImageUrl($this->front_image);
    }

    /**
     * Full URL of document back image.
     */
    public function getBackImageUrlAttribute(): ?string
    {
        return static::resolveImageUrl($this->back_image);
    }

    /**
     * Full URL of selfie with document / face scan image.
     */
    public function getSelfieImageUrlAttribute(): ?string
    {
        return static::resolveImageUrl($this->selfie_image);
    }

    /**
     * Full URL of Center face image.
     */
    public function getFaceCenterImageUrlAttribute(): ?string
    {
        return static::resolveImageUrl($this->face_center_image ?: $this->selfie_image);
    }

    /**
     * Full URL of Left side face image.
     */
    public function getFaceLeftImageUrlAttribute(): ?string
    {
        return static::resolveImageUrl($this->face_left_image);
    }

    /**
     * Full URL of Right side face image.
     */
    public function getFaceRightImageUrlAttribute(): ?string
    {
        return static::resolveImageUrl($this->face_right_image);
    }

    /**
     * Full URL of Eye Blink face image.
     */
    public function getFaceBlinkImageUrlAttribute(): ?string
    {
        return static::resolveImageUrl($this->face_blink_image);
    }

    /**
     * Human-readable document type label.
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'nid'               => 'National ID Card (NID)',
            'passport'          => 'International Passport',
            'birth_certificate' => 'Birth Certificate (DOB)',
            default             => strtoupper($this->document_type),
        };
    }

    /**
     * CSS badge class for status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default    => 'badge-warning',
        };
    }
}
