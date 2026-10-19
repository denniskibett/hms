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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->index('invoice_items_invoice_id_foreign');
            $table->enum('source_type', ['room', 'food', 'facility', 'service', 'other']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->decimal('quantity', 10)->default(1);
            $table->decimal('unit_price', 12);
            $table->decimal('total', 12)->nullable()->virtualAs('`quantity` * `unit_price`');
            $table->timestamps();

            $table->index(['source_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
