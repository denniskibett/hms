<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock movements. inventory_items.quantity is the running balance; every
 * change to it is written here with the signed change and the balance after,
 * so stock can always be traced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            // in = received, out = issued / used, adjustment = stock count, damage / expired = written off
            $table->enum('type', ['in', 'out', 'adjustment', 'damage', 'expired']);
            $table->decimal('quantity', 12, 2)->comment('Signed change: + in, - out');
            $table->decimal('balance_after', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_value', 14, 2)->default(0);
            $table->string('reference_type', 30)->nullable()->comment('purchase_order, task, manual');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['inventory_item_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
