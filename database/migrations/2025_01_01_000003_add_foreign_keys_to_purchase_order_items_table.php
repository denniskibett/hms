<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreign(['inventory_item_id'])->references(['id'])->on('inventory_items')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['purchase_order_id'])->references(['id'])->on('purchase_orders')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign('purchase_order_items_inventory_item_id_foreign');
            $table->dropForeign('purchase_order_items_purchase_order_id_foreign');
        });
    }
};
