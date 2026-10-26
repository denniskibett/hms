<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Repeating tasks: make the copies that fall due (also done hourly when Tasks is opened)
Artisan::command('tasks:repeat', function () {
    $made = \App\Models\Task::generateRecurring();
    $this->info("{$made} repeating " . \Illuminate\Support\Str::plural('task', $made) . ' created.');
})->purpose('Create the next copies of repeating tasks');

\Illuminate\Support\Facades\Schedule::command('tasks:repeat')->dailyAt('00:10');
