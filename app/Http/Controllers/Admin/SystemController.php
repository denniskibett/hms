<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System;
use App\Helpers\SystemHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Alcohol\ISO4217;

class SystemController extends Controller
{
    public function index()
    {
        $appName = SystemHelper::appName();
        $logoUrl = SystemHelper::logoUrl();
        $authLogoUrl = SystemHelper::authLogoUrl();
        $slogan = SystemHelper::slogan();
        $primaryColor = SystemHelper::primaryColor();
        $system = System::settings();
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $iso4217 = new ISO4217();

        // Fetch all currencies
        $currencies = [];
        foreach ($iso4217->getAll() as $currency) {
            $currencies[$currency['alpha3']] = [
                'name' => $currency['name'],
                'symbol' => $currency['symbol'] ?? '',
            ];
        }


        return view('admin.system.simple', compact('system', 'timezones', 'currencies', 'appName', 'logoUrl', 'authLogoUrl', 'slogan', 'primaryColor'));
    }

    public function update(Request $request)
    {
        $system = System::settings();
        
        // Basic validation
        $request->validate([
            'name' => 'required|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:10',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'pagination_limit' => 'required|integer|min:5|max:100',
            // No SVG: it can carry script and is served from our own domain
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon' => 'nullable|file|mimes:png,ico|max:1024',
            'contact_email' => 'nullable|email|max:255',
            'login_attempts' => 'nullable|integer|min:1|max:20',
            'session_timeout' => 'nullable|integer|min:5|max:1440',
            'password_expiry' => 'nullable|integer|min:0|max:365',
            'backup_frequency' => 'nullable|in:daily,weekly,monthly',
            'backup_retention' => 'nullable|integer|min:1|max:365',
        ]);

        // Prepare data for update
        $data = $request->only([
            'name', 'slogan', 'timezone', 'date_format', 'time_format',
            'currency', 'currency_symbol', 'primary_color', 'secondary_color',
            'contact_email', 'contact_phone', 'address', 'meta_description',
            'meta_keywords', 'facebook_url', 'twitter_url', 'instagram_url',
            'linkedin_url', 'pagination_limit', 'custom_css', 'custom_js'
        ]);

        // Handle file uploads
        // store() names the file by hash with an extension guessed from its content,
        // never the client-supplied one
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('system', 'public');

            // Delete old logo
            if ($system->logo) {
                Storage::disk('public')->delete($system->logo);
            }
        }

        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('system', 'public');

            // Delete old favicon
            if ($system->favicon) {
                Storage::disk('public')->delete($system->favicon);
            }
        }

        // Handle maintenance mode
        $data['maintenance_mode'] = $request->boolean('maintenance_mode');

        // Prepare settings array
        $settings = $system->settings ?? [];
        
        // Update notification settings
        $settings['notifications'] = [
            'email_notifications' => $request->boolean('email_notifications'),
            'push_notifications' => $request->boolean('push_notifications'),
            'sms_notifications' => $request->boolean('sms_notifications', false),
            'notification_sound' => $request->boolean('notification_sound', true),
        ];

        // Update security settings
        $settings['security'] = [
            'two_factor_auth' => $request->boolean('two_factor_auth'),
            'login_attempts' => $request->integer('login_attempts', 5),
            'session_timeout' => $request->integer('session_timeout', 30),
            'password_expiry' => $request->integer('password_expiry', 90),
        ];

        // Update integrations
        $settings['integrations'] = [
            'google_analytics' => $request->input('google_analytics', ''),
            'google_maps_key' => $request->input('google_maps_key', ''),
        ];

        // Update backup settings
        $settings['backup'] = [
            'auto_backup' => $request->boolean('auto_backup', true),
            'backup_frequency' => $request->input('backup_frequency', 'daily'),
            'backup_retention' => $request->integer('backup_retention', 30),
            'backup_to_cloud' => $request->boolean('backup_to_cloud', false),
        ];

        $data['settings'] = $settings;

        // Update system settings
        $system->update($data);

        // Clear cache
        Cache::forget('system_settings');

        return redirect()->route('system.index')->with('success', 'Settings updated successfully!');
    }

    public function clearCache()
    {
        Cache::flush();
        return back()->with('success', 'Cache cleared successfully!');
    }

    public function toggleMaintenance()
    {
        $system = System::settings();
        $system->update([
            'maintenance_mode' => !$system->maintenance_mode
        ]);
        
        Cache::forget('system_settings');
        return back()->with('success', 'Maintenance mode updated!');
    }
}