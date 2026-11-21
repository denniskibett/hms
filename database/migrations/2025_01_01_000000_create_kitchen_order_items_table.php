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
        Schema::create('kitchen_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->index('kitchen_order_items_order_id_foreign');
            $table->unsignedBigInteger('menu_item_variant_id')->index('kitchen_order_items_menu_item_variant_id_foreign');
            $table->integer('quantity');
            $table->decimal('price_at_order', 10);
            $table->enum('status', ['pending', 'preparing', 'ready', 'delivered'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_order_items');
    }
};
