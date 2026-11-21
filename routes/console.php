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

// Paid invoices that KRA eTIMS hasn't signed yet (network down, KRA busy): try again
Artisan::command('etims:retry', function () {
    $etims = app(\App\Services\Etims\Etims::class);
    if (! \App\Services\Etims\Etims::enabled()) {
        $this->info('eTIMS is off.');

        return;
    }
    $sent = 0;
    \App\Models\Invoice::where('status', 'paid')
        ->where(fn ($q) => $q->whereDoesntHave('etimsReceipt')->orWhereHas('etimsReceipt', fn ($r) => $r->where('status', '!=', 'sent')->where('attempts', '<', 20)))
        ->orderBy('id')->limit(100)->get()
        ->each(function ($invoice) use ($etims, &$sent) {
            $sent += $etims->submit($invoice)?->isSent() ? 1 : 0;
        });
    $this->info("Signed {$sent} invoice(s).");
})->purpose('Send paid invoices that KRA eTIMS has not signed yet');
\Illuminate\Support\Facades\Schedule::command('etims:retry')->hourly()->withoutOverlapping();

// Close yesterday if nobody did it by hand
Artisan::command('hotel:night-audit {date?}', function (?string $date = null) {
    $day = $date ? \Carbon\Carbon::parse($date) : today()->subDay();
    if (! $date && \App\Models\NightAudit::whereDate('business_date', $day)->exists()) {
        $this->info($day->toDateString() . ' was already closed by hand.');

        return;
    }
    $record = app(\App\Services\NightAudit::class)->run($day);
    $this->info('Closed ' . $record->business_date->toDateString() . ': ' . $record->figure('rooms_sold') . ' rooms sold, ' . count($record->figure('no_shows', [])) . ' no-show(s).');
})->purpose('Night audit: mark no-shows, list overstays and keep the day\'s figures');
\Illuminate\Support\Facades\Schedule::command('hotel:night-audit')->dailyAt('03:00')->withoutOverlapping();
