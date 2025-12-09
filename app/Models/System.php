<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    use HasFactory;

    protected $table = 'system';

    protected $fillable = [
        'name', 'logo', 'favicon', 'slogan', 'timezone',
        'date_format', 'time_format', 'currency', 'currency_symbol',
        'primary_color', 'secondary_color', 'contact_email', 'contact_phone',
        'address', 'meta_description', 'meta_keywords',
        'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url',
        'maintenance_mode', 'pagination_limit', 'custom_css', 'custom_js',
        'settings'
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'settings' => 'array',
    ];

    // Default settings structure
    protected $attributes = [
        'settings' => '{
            "notifications": {
                "email_notifications": true,
                "push_notifications": true,
                "sms_notifications": false,
                "notification_sound": true
            },
            "security": {
                "two_factor_auth": false,
                "login_attempts": 5,
                "session_timeout": 30,
                "password_expiry": 90
            },
            "integrations": {
                "google_analytics": "",
                "google_maps_key": "",
                "mail_driver": "smtp",
                "mail_host": "",
                "mail_port": "587",
                "mail_username": "",
                "mail_password": ""
            },
            "backup": {
                "auto_backup": true,
                "backup_frequency": "daily",
                "backup_retention": 30,
                "backup_to_cloud": false
            }
        }'
    ];

    public static function settings()
    {
        return self::firstOrCreate([], [
            'name' => config('app.name', 'Laravel'),
            'timezone' => config('app.timezone', 'UTC'),
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'primary_color' => '#3A57E8',
            'secondary_color' => '#08B1BA',
            'pagination_limit' => 15,
        ]);
    }
}