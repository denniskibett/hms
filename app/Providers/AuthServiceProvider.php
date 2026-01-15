<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\Stay;
use App\Policies\StayPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Stay::class => StayPolicy::class,

    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
