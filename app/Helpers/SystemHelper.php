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
                'date_format' => 'd-m-Y',
                'time_format' => 'H:i:s',
                'currency' => 'KES',
                'currency_symbol' => 'KES',
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
                    // Add About Us
                    'about_us' => [
                        'title' => 'About Us',
                        'subtitle' => "South Rift's Finest",
                        'description' => "Nearness to the lake region counties which includes Narok county, Kericho, Kisii, Migori, Homabay and Kisumu counties respectively. In addition, it’s a gateway to the neighbouring countries of Uganda and Tanzania. It’s also a gateway to the Mau forest and the Tea growing highlands of both Bomet and Kericho counties. It’s within the Maasai Mara tourist circuit and a link to the North Rift of Nakuru and Eldoret tourist sites and hospitality industry in the region.",
                        'extra' => "So when it comes to booking the perfect hotel, vacation rental, resort, apartment, guest house, or tree house, we’ve got you covered.",
                        'images' => [
                            asset('twh/img/about/about-1.jpg'),
                            asset('twh/img/about/about-2.jpg')
                        ]
                    ],
                    // Add Services
                    'services' => [
                        [
                            'icon' => 'flaticon-036-parking',
                            'title' => 'Travel Plan',
                            'description' => 'Plan your travel with ease and comfort.'
                        ],
                        [
                            'icon' => 'flaticon-033-dinner',
                            'title' => 'Catering Service',
                            'description' => 'Enjoy quality meals and catering service.'
                        ],
                        [
                            'icon' => 'flaticon-026-bed',
                            'title' => 'Babysitting',
                            'description' => 'Professional babysitting services for your kids.'
                        ],
                        [
                            'icon' => 'flaticon-024-towel',
                            'title' => 'Laundry',
                            'description' => 'Quick and reliable laundry services.'
                        ],
                        [
                            'icon' => 'flaticon-044-clock-1',
                            'title' => 'Hire Driver',
                            'description' => 'Safe and convenient transportation services.'
                        ],
                        [
                            'icon' => 'flaticon-012-cocktail',
                            'title' => 'Bar & Drink',
                            'description' => 'Relax with drinks at our bar.'
                        ],
                    ]
                ],
            ]);
        });
    }


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

    public static function appName()
    {
        return self::get('name', config('app.name'));
    }

    public static function slogan()
    {
        return self::get('slogan', 'Your trusted application');
    }

    public static function logoUrl($dark = false, $icon = false)
    {
        $system = Cache::rememberForever('system_settings', function () {
            return System::firstOrCreate([]);
        });

        if ($icon) {
            return $system->logo_icon ? asset('images/logo/' . basename($system->logo_icon)) : asset('images/logo/logo-icon.svg');
        }

        if ($dark) {
            return $system->logo_dark ? asset('images/logo/' . basename($system->logo_dark)) : asset('images/logo/logo-dark.svg');
        }

        return $system->logo ? asset('images/logo/' . basename($system->logo)) : asset('images/logo/logo.svg');
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

    public static function aboutUs()
    {
        $settings = self::settings();

        // Get the about_us JSON field
        $aboutUs = $settings->about_us ?? null;

        // Decode if it's stored as a JSON string
        if (is_string($aboutUs)) {
            $aboutUs = json_decode($aboutUs, true);
        }

        // Fallback default if empty
        return $aboutUs ?? [
            'title' => 'About Us',
            'subtitle' => "South Rift's Finest",
            'description' => "Nearness to the lake region counties which includes Narok county, Kericho, Kisii, Migori, Homabay and Kisumu counties respectively. In addition, it’s a gate way to the neighbouring countries of Uganda and Tanzania. It’s also a gateway to the Mau forest and the Tea growing highlands of both Bomet and Kericho counties. It’s within the Maasai Mara tourist circuit and a link to the North Rift of Nakuru and Eldoret tourist sites and hospitality industry in the region.",
            'extra' => "So when it comes to booking the perfect hotel, vacation rental, resort, apartment, guest house, or tree house, we’ve got you covered.",
        ];
    }


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
        $symbol = self::get('currency_symbol', 'KES');
        $position = self::get('settings.currency_position', 'before');
        
        if ($position === 'after') {
            return number_format($amount, 2) . $symbol;
        }
        
        return $symbol . number_format($amount, 2);
    }


    public static function primaryColor()
    {
        return self::get('primary_color', '#3A57E8');
    }


    public static function secondaryColor()
    {
        return self::get('secondary_color', '#08B1BA');
    }


    public static function contactEmail()
    {
        return self::get('contact_email');
    }

    public static function socials()
    {
        $system = System::first();
        
        // Decode JSON into an array, fallback to empty array
        return $system && $system->socials ? json_decode($system->socials, true) : [];
    }


    public static function contactPhone()
    {
        return self::get('contact_phone');
    }


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
        return self::get('date_format', 'd-m-Y');
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
        return self::get('currency', 'KES');
    }

    /**
     * Get currency symbol
     */
    public static function currencySymbol()
    {
        return self::get('currency_symbol', 'KES');
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

    public static function services()
    {
        $settings = self::settings();

        $services = $settings->services ?? null;

        if (is_string($services)) {
            $services = json_decode($services, true);
        }

        return $services ?? [
            [
                "icon" => "flaticon-036-parking",
                "title" => "Conferencing",
                "description" => "Create your own meaningful connections in modern spaces designed for sharing, socializing, and collaborating."
            ],
            [
                "icon" => "flaticon-033-dinner",
                "title" => "Restaurant",
                "description" => "Come dine at our main restaurant after your busy day and sample the best of Bomet's finest cuisine."
            ],
            [
                "icon" => "flaticon-026-bed",
                "title" => "Accommodation",
                "description" => "Enjoy comfortable rooms with regionally inspired artwork and spacious suites with residential features."
            ],
            [
                "icon" => "flaticon-044-clock-1",
                "title" => "Events",
                "description" => "Versatile venues. Dedicated event planners. Customizable catering. Wedding packages. All of this and more..."
            ],
            [
                "icon" => "flaticon-012-cocktail",
                "title" => "Tour Bomet",
                "description" => "Bomet County bursts with a lot of life and tourism. Do not be left behind. Explore and discover."
            ],
            [
                "icon" => "flaticon-024-towel",
                "title" => "Bar & Drink",
                "description" => "Think spirited restaurants and bars, vibrant event venues, and their trademark atrium setting—each one distinct from the next."
            ]
        ];
    }


}