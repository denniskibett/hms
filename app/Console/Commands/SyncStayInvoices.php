<?php

namespace App\Console\Commands;

use App\Models\Stay;
use App\Services\BillingService;
use Illuminate\Console\Command;

/**
 * Rebuild stay invoices from their bookings (room + service lines, taxes,
 * paid amounts). Safe to run repeatedly; other invoice lines are kept.
 */
class SyncStayInvoices extends Command
{
    protected $signature = 'hms:sync-invoices {--stay= : Only this stay id}';

    protected $description = 'Create or refresh the invoice of every stay from its rooms and services';

    public function handle(BillingService $billing): int
    {
        $stays = Stay::query()
            ->when($this->option('stay'), fn ($q, $id) => $q->whereKey($id))
            ->orderBy('id')
            ->get();

        $this->withProgressBar($stays, fn (Stay $stay) => $billing->syncStayInvoice($stay));
        $this->newLine();
        $this->info("Synced {$stays->count()} stays.");

        return self::SUCCESS;
    }
}
