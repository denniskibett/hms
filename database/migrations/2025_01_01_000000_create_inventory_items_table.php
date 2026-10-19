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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->enum('item_type', ['cleaning', 'kitchen', 'office', 'maintenance', 'food', 'beverage', 'linen', 'amenity', 'other'])->default('other');
            $table->enum('category', ['consumable', 'non_consumable'])->default('consumable');
            $table->text('description')->nullable();
            $table->string('unit_of_measure');
            $table->decimal('quantity', 10)->default(0);
            $table->decimal('reorder_level', 10)->default(0);
            $table->decimal('unit_cost', 10)->default(0);
            $table->unsignedBigInteger('primary_supplier_id')->nullable()->index('inventory_items_primary_supplier_id_foreign');
            $table->decimal('minimum_stock', 10)->nullable();
            $table->decimal('maximum_stock', 10)->nullable();
            $table->string('location')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
