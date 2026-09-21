<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FirebaseApp extends Model
{
    use HasFactory;

    protected $table = 'firebase_apps';

    protected $fillable = [
        'app_name',
        'package_name',
        'service_account_json',
        'service_account_path',
        'server_key',
        'project_id',
        'client_email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get device registrations linked to this Firebase App.
     */
    public function deviceRegistrations(): HasMany
    {
        return $this->hasMany(DeviceRegistration::class, 'firebase_app_id');
    }

    /**
     * Get push notifications sent via this app.
     */
    public function pushNotifications(): HasMany
    {
        return $this->hasMany(PushNotification::class, 'firebase_app_id');
    }

    /**
     * Get the default/active primary Firebase App.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
