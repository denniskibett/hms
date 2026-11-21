<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Sign-in, sign-up and password pages: <x-guest-layout title="…" subtitle="…">.
 */
class GuestLayout extends Component
{
    public function __construct(public ?string $title = null, public ?string $subtitle = null) {}

    public function render(): View
    {
        return view('layouts.guest');
    }
}
