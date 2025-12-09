<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register SystemHelper as a singleton
        $this->app->singleton('system.helper', function ($app) {
            return new \App\Helpers\SystemHelper();
        });
        
        // Create alias for easier access
        class_alias(\App\Helpers\SystemHelper::class, 'SystemHelper');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // You can also publish configs or run migrations here if needed
    }
}