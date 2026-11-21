<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Money paid out: running costs (power, rent, fuel…) and payments to
 * suppliers. A supplier payment names the supplier and, optionally, the
 * purchase order it settles. Mistakes are voided, never deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->date('spent_on');
            $table->string('category', 40);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'credit_card', 'mobile_money', 'bank_transfer', 'cheque']);
            $table->string('reference', 100)->nullable()->comment('M-Pesa code, cheque or bank ref');
            $table->foreignId('supplier_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['paid', 'void'])->default('paid');
            $table->string('void_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['spent_on', 'status']);
            $table->index(['category', 'spent_on']);
            $table->index(['supplier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
