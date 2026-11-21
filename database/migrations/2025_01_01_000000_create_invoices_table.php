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
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stay_id')->index('invoices_stay_id_foreign');
            $table->string('invoice_number')->unique();
            $table->decimal('subtotal', 12)->default(0);
            $table->decimal('tax_amount', 12)->default(0);
            $table->decimal('discount_amount', 12)->default(0);
            $table->decimal('total', 12)->default(0);
            $table->decimal('paid_amount', 12)->default(0);
            $table->decimal('due_amount', 12)->nullable()->virtualAs('`total` - `paid_amount`');
            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('issue_date');
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
