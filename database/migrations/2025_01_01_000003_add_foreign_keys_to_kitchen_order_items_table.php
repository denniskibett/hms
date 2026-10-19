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
        Schema::table('kitchen_order_items', function (Blueprint $table) {
            $table->foreign(['menu_item_variant_id'])->references(['id'])->on('menu_item_variants')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['order_id'])->references(['id'])->on('kitchen_orders')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_order_items', function (Blueprint $table) {
            $table->dropForeign('kitchen_order_items_menu_item_variant_id_foreign');
            $table->dropForeign('kitchen_order_items_order_id_foreign');
        });
    }
};
