<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\System;
use App\Helpers\SystemHelper;

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
        System::updated(function ($system) {
            SystemHelper::clearCache();
        });
    }
}
