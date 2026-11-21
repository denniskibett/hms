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
