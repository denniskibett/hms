<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kitchen orders serve walk-in customers as well as in-house guests:
 * - stay_id is optional; charge_to_room puts the order on the stay's invoice
 * - walk-in orders get their own invoice (invoices.kitchen_order_id)
 * - table_number / customer_name say who the food is for
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitchen_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('stay_id')->nullable()->change();
            $table->boolean('charge_to_room')->default(false)->after('stay_id');
            $table->string('customer_name')->nullable()->after('type');
            $table->string('table_number', 20)->nullable()->after('customer_name');
            $table->string('cancel_reason')->nullable()->after('delivered_at');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('kitchen_order_id')->nullable()->after('facility_booking_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kitchen_order_id');
        });

        Schema::table('kitchen_orders', function (Blueprint $table) {
            $table->dropColumn(['charge_to_room', 'customer_name', 'table_number', 'cancel_reason']);
        });
    }
};
