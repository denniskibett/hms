<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Payroll, expenses and staff records brought up to Breve's journeys (additive only).
 * Live gets the same change by hand: database/schema/2026_11_21_breve_payroll_expenses_staff.sql.
 *
 *  - staff_profiles: pay basis (salary with statutory deductions, or stipend), named allowances and deductions,
 *    insurance premium (insurance relief), pay method and M-Pesa number for the bulk-pay file.
 *  - payrolls: employer NSSF and housing levy, insurance relief, and every payslip line (breakdown).
 *  - expenses: awaiting approval -> approved -> paid (or rejected), receipt, who approved and when it was paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('pay_basis', 20)->default('statutory')->after('salary');
            $table->json('allowances')->nullable()->after('pay_basis');
            $table->json('deductions')->nullable()->after('allowances');
            $table->decimal('insurance_premium', 12, 2)->default(0)->after('deductions');
            $table->string('pay_method', 10)->default('bank')->after('insurance_premium');
            $table->string('mpesa_phone', 20)->nullable()->after('pay_method');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('insurance_relief', 12, 2)->default(0)->after('other_deductions');
            $table->decimal('employer_nssf', 12, 2)->default(0)->after('insurance_relief');
            $table->decimal('employer_housing_levy', 12, 2)->default(0)->after('employer_nssf');
            $table->json('breakdown')->nullable()->after('employer_housing_levy');
        });

        DB::statement("ALTER TABLE expenses MODIFY method ENUM('cash','credit_card','mobile_money','bank_transfer','cheque') NULL");
        DB::statement("ALTER TABLE expenses MODIFY status ENUM('pending','approved','paid','rejected','void') NOT NULL DEFAULT 'paid'");
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('reference');
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('paid_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['receipt_path', 'approved_at', 'paid_at']);
        });
        DB::statement("ALTER TABLE expenses MODIFY status ENUM('paid','void') NOT NULL DEFAULT 'paid'");
        DB::statement("ALTER TABLE expenses MODIFY method ENUM('cash','credit_card','mobile_money','bank_transfer','cheque') NOT NULL");
        Schema::table('payrolls', fn (Blueprint $table) => $table->dropColumn(['insurance_relief', 'employer_nssf', 'employer_housing_levy', 'breakdown']));
        Schema::table('staff_profiles', fn (Blueprint $table) => $table->dropColumn(['pay_basis', 'allowances', 'deductions', 'insurance_premium', 'pay_method', 'mpesa_phone']));
    }
};
