<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Procurement rebuild:
 * - suppliers get a KRA PIN, payment terms and notes
 * - purchase orders can be received in parts (received_quantity per line,
 *   status partially_received) and record the supplier's delivery / invoice ref
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('kra_pin', 20)->nullable()->after('email');
            $table->string('payment_terms', 100)->nullable()->after('address');
            $table->text('notes')->nullable()->after('payment_terms');
        });

        DB::statement("ALTER TABLE purchase_orders MODIFY status ENUM('draft', 'submitted', 'approved', 'ordered', 'partially_received', 'received', 'cancelled') NOT NULL DEFAULT 'draft'");

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('supplier_reference', 100)->nullable()->after('notes')->comment('Supplier invoice / delivery note no.');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('received_quantity', 10)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('received_quantity');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('supplier_reference');
        });

        DB::statement("ALTER TABLE purchase_orders MODIFY status ENUM('draft', 'submitted', 'approved', 'ordered', 'received', 'cancelled') NOT NULL DEFAULT 'draft'");

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['kra_pin', 'payment_terms', 'notes']);
        });
    }
};
