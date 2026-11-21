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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('period_month');
            $table->decimal('basic_salary', 12);
            $table->decimal('overtime_amount', 12)->default(0);
            $table->decimal('allowances', 12)->default(0);
            $table->decimal('bonuses', 12)->default(0);
            $table->decimal('gross', 12)->nullable()->virtualAs('`basic_salary` + `overtime_amount` + `allowances` + `bonuses`');
            $table->decimal('tax_deductions', 12)->default(0);
            $table->decimal('other_deductions', 12)->default(0);
            $table->decimal('net', 12)->nullable()->virtualAs('`gross` - `tax_deductions` - `other_deductions`');
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index('payrolls_approved_by_foreign');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['period_month', 'status']);
            $table->unique(['user_id', 'period_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
