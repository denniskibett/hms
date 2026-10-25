<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a guest's user account used to cascade to their stays, the stays'
 * invoices and the invoices' payments, erasing records the hotel must keep
 * for tax. The database now refuses such deletes; the app closes the account
 * instead (stays are soft-deleted, invoices cancelled, payments voided).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stays', function (Blueprint $table) {
            $table->dropForeign('stays_guest_id_foreign');
            $table->foreign('guest_id', 'stays_guest_id_foreign')->references('id')->on('users')->restrictOnUpdate()->restrictOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_stay_id_foreign');
            $table->foreign('stay_id', 'invoices_stay_id_foreign')->references('id')->on('stays')->restrictOnUpdate()->restrictOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_invoice_id_foreign');
            $table->foreign('invoice_id', 'payments_invoice_id_foreign')->references('id')->on('invoices')->restrictOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_invoice_id_foreign');
            $table->foreign('invoice_id', 'payments_invoice_id_foreign')->references('id')->on('invoices')->restrictOnUpdate()->cascadeOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_stay_id_foreign');
            $table->foreign('stay_id', 'invoices_stay_id_foreign')->references('id')->on('stays')->restrictOnUpdate()->cascadeOnDelete();
        });

        Schema::table('stays', function (Blueprint $table) {
            $table->dropForeign('stays_guest_id_foreign');
            $table->foreign('guest_id', 'stays_guest_id_foreign')->references('id')->on('users')->restrictOnUpdate()->cascadeOnDelete();
        });
    }
};
