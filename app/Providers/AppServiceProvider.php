<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\System;
use App\Helpers\SystemHelper;
use PragmaRX\Countries\Package\Countries; 
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Admins pass every policy / authorize() check
        Gate::before(function ($user) {
            return $user->hasRole('admin') ? true : null;
        });

        // Clear cache on system update
        System::updated(function ($system) {
            SystemHelper::clearCache();
        });

        // Make countries available globally for guest views
        View::composer(['guest.*'], function ($view) {
            try {
                $countries = (new Countries())->all()
                    ->map(function ($country) {
                        return [
                            'name' => $country->name->common ?? '',
                            'official_name' => $country->name->official ?? '',
                            'cca2' => $country->cca2 ?? '',
                            'flag_emoji' => $country->flag->emoji ?? '',
                        ];
                    })
                    ->sortBy('name')
                    ->values()
                    ->toArray();
            } catch (\Exception $e) {

                $countries = [
                    ['name' => 'United States', 'cca2' => 'US', 'flag_emoji' => '🇺🇸'],
                    ['name' => 'United Kingdom', 'cca2' => 'GB', 'flag_emoji' => '🇬🇧'],
                    ['name' => 'Canada', 'cca2' => 'CA', 'flag_emoji' => '🇨🇦'],
                    ['name' => 'Australia', 'cca2' => 'AU', 'flag_emoji' => '🇦🇺'],
                    ['name' => 'Germany', 'cca2' => 'DE', 'flag_emoji' => '🇩🇪'],
                    ['name' => 'France', 'cca2' => 'FR', 'flag_emoji' => '🇫🇷'],
                    ['name' => 'Japan', 'cca2' => 'JP', 'flag_emoji' => '🇯🇵'],
                    ['name' => 'China', 'cca2' => 'CN', 'flag_emoji' => '🇨🇳'],
                ];
            }

            $view->with('countries', $countries);
        });
    }
}
