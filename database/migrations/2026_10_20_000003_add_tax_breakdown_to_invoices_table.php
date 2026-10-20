<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keep the rates an invoice was issued with, so later rate changes
     * never alter old invoices or receipts.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('tax_inclusive')->default(true)->after('tax_amount');
            $table->decimal('vat_rate', 5, 2)->default(0)->after('tax_inclusive');
            $table->decimal('vat_amount', 12, 2)->default(0)->after('vat_rate');
            $table->decimal('levy_rate', 5, 2)->default(0)->after('vat_amount');
            $table->decimal('levy_amount', 12, 2)->default(0)->after('levy_rate');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['tax_inclusive', 'vat_rate', 'vat_amount', 'levy_rate', 'levy_amount']);
        });
    }
};
