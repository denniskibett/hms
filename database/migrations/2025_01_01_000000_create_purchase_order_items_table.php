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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('inventory_item_id')->index('purchase_order_items_inventory_item_id_foreign');
            $table->decimal('quantity', 10);
            $table->decimal('unit_price', 10);
            $table->decimal('total', 12)->nullable()->virtualAs('`quantity` * `unit_price`');
            $table->timestamps();

            $table->unique(['purchase_order_id', 'inventory_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
