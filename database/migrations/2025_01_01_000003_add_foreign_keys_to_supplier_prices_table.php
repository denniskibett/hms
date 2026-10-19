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
        Schema::table('supplier_prices', function (Blueprint $table) {
            $table->foreign(['inventory_item_id'])->references(['id'])->on('inventory_items')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['supplier_id'])->references(['id'])->on('suppliers')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_prices', function (Blueprint $table) {
            $table->dropForeign('supplier_prices_inventory_item_id_foreign');
            $table->dropForeign('supplier_prices_supplier_id_foreign');
        });
    }
};
