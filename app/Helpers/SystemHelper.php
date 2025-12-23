<?php

namespace App\Helpers;

use App\Models\System;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemHelper
{
    /**
     * Get system settings with caching
     */
    public static function settings()
    {
        return Cache::rememberForever('system_settings', function () {
            return System::firstOrCreate([], [
                'name' => config('app.name', 'Laravel'),
                'slogan' => 'Your trusted application',
                'timezone' => config('app.timezone', 'UTC'),
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'primary_color' => '#3A57E8',
                'secondary_color' => '#08B1BA',
                'pagination_limit' => 15,
                'maintenance_mode' => false,
                'settings' => [
                    'notifications' => [
                        'email_notifications' => true,
                        'push_notifications' => true,
                        'sms_notifications' => false,
                        'notification_sound' => true,
                    ],
                    'security' => [
                        'two_factor_auth' => false,
                        'login_attempts' => 5,
                        'session_timeout' => 30,
                        'password_expiry' => 90,
                    ],
                    'integrations' => [
                        'google_analytics' => '',
                        'google_maps_key' => '',
                        'mail_driver' => 'smtp',
                        'mail_host' => '',
                        'mail_port' => '587',
                        'mail_username' => '',
                        'mail_password' => '',
                    ],
                    'backup' => [
                        'auto_backup' => true,
                        'backup_frequency' => 'daily',
                        'backup_retention' => 30,
                        'backup_to_cloud' => false,
                    ],
                ],
            ]);
        });
    }

    /**
     * Clear system settings cache
     */
    public static function clearCache()
    {
        Cache::forget('system_settings');
    }

    /**
     * Get specific setting
     */
    public static function get($key, $default = null)
    {
        $settings = self::settings();
        
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $value = $settings->settings ?? [];
            
            foreach ($keys as $k) {
                if (is_array($value) && array_key_exists($k, $value)) {
                    $value = $value[$k];
                } else {
                    return $default;
                }
            }
            
            return $value;
        }
        
        return $settings->$key ?? $default;
    }

    /**
     * Get application name
     */
    public static function appName()
    {
        return self::get('name', config('app.name'));
    }

    public static function slogan()
    {
        return self::get('slogan', 'Your trusted application');
    }

    /**
     * Get logo URL (with icon support)
     */
    public static function logoUrl($icon = false)
    {
        $logo = self::get('logo');
        
        if ($logo) {
            try {
                $url = asset('storage/' . $logo);
                // You could add logic here to return different URLs for icon vs full logo
                // For now, same URL for both
                return $url;
            } catch (\Exception $e) {
                // Fallback to default if there's an error
            }
        }
        
        // Default fallback logos
        return $icon 
            ? asset('images/logo/logo-icon.svg')
            : asset('images/logo/logo.svg');
    }

    public static function authLogoUrl()
    {
        $logo = self::get('logo');
        
        if ($logo) {
            try {
                return asset('storage/' . $logo);
            } catch (\Exception $e) {
                // Fallback to default
            }
        }
        
        return asset('images/logo/auth-logo.svg');
    }

    public static function faviconUrl()
    {
        $favicon = self::get('favicon');
        
        if ($favicon) {
            try {
                return asset('storage/' . $favicon);
            } catch (\Exception $e) {
                // Fallback to default
            }
        }
        
        return asset('images/favicon.ico');
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode()
    {
        return (bool) self::get('maintenance_mode', false);
    }

    /**
     * Get pagination limit
     */
    public static function paginationLimit()
    {
        return self::get('pagination_limit', 15);
    }

    /**
     * Get currency with symbol
     */
    public static function currency($amount)
    {
        $symbol = self::get('currency_symbol', '$');
        $position = self::get('settings.currency_position', 'before');
        
        if ($position === 'after') {
            return number_format($amount, 2) . $symbol;
        }
        
        return $symbol . number_format($amount, 2);
    }

    /**
     * Get primary color
     */
    public static function primaryColor()
    {
        return self::get('primary_color', '#3A57E8');
    }

    /**
     * Get secondary color
     */
    public static function secondaryColor()
    {
        return self::get('secondary_color', '#08B1BA');
    }

    /**
     * Get contact email
     */
    public static function contactEmail()
    {
        return self::get('contact_email');
    }

    /**
     * Get contact phone
     */
    public static function contactPhone()
    {
        return self::get('contact_phone');
    }

    /**
     * Get address
     */
    public static function address()
    {
        return self::get('address');
    }

    /**
     * Get social media URLs
     */
    public static function socialMedia($platform = null)
    {
        $social = [
            'facebook' => self::get('facebook_url'),
            'twitter' => self::get('twitter_url'),
            'instagram' => self::get('instagram_url'),
            'linkedin' => self::get('linkedin_url'),
        ];
        
        return $platform ? ($social[$platform] ?? null) : $social;
    }

    /**
     * Get meta description
     */
    public static function metaDescription()
    {
        return self::get('meta_description');
    }

    /**
     * Get meta keywords
     */
    public static function metaKeywords()
    {
        return self::get('meta_keywords');
    }

    /**
     * Get timezone
     */
    public static function timezone()
    {
        return self::get('timezone', config('app.timezone', 'UTC'));
    }

    /**
     * Get date format
     */
    public static function dateFormat()
    {
        return self::get('date_format', 'Y-m-d');
    }

    /**
     * Get time format
     */
    public static function timeFormat()
    {
        return self::get('time_format', 'H:i:s');
    }

    /**
     * Get currency code
     */
    public static function currencyCode()
    {
        return self::get('currency', 'USD');
    }

    /**
     * Get currency symbol
     */
    public static function currencySymbol()
    {
        return self::get('currency_symbol', '$');
    }

    /**
     * Get custom CSS
     */
    public static function customCss()
    {
        return self::get('custom_css');
    }

    /**
     * Get custom JavaScript
     */
    public static function customJs()
    {
        return self::get('custom_js');
    }

    public static function googleAnalyticsId()
    {
        // return your GA ID or leave null
        return config('services.google.analytics_id');
    }

    public static function twoFactorAuthEnabled()
    {
        $settings = System::settings()->settings ?? [];

        return $settings['security']['two_factor_auth'] ?? false;
    }

}