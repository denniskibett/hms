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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id')->index('purchase_orders_supplier_id_foreign');
            $table->string('po_number')->unique();
            $table->unsignedBigInteger('requested_by')->index('purchase_orders_requested_by_foreign');
            $table->enum('status', ['draft', 'submitted', 'approved', 'ordered', 'received', 'cancelled'])->default('draft');
            $table->date('delivery_date')->nullable();
            $table->decimal('total', 12)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index('purchase_orders_approved_by_foreign');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
