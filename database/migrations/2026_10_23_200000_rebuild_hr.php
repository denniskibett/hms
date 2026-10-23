<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HR rebuild:
 * - staff_profiles get the details Kenyan payroll and HR files need
 *   (job title, ID, KRA PIN, NSSF and SHA numbers, end date)
 * - payrolls keep each statutory deduction separately for the payslip;
 *   tax_deductions stays their total so the virtual `net` column still works
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('department_id');
            $table->string('id_number', 30)->nullable()->after('job_title');
            $table->string('kra_pin', 20)->nullable()->after('id_number');
            $table->string('nssf_number', 30)->nullable()->after('kra_pin');
            $table->string('shif_number', 30)->nullable()->after('nssf_number');
            $table->date('end_date')->nullable()->after('hire_date');
            $table->text('notes')->nullable()->after('emergency_contact');
            $table->unique('user_id');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('overtime_hours', 6)->default(0)->after('basic_salary');
            $table->decimal('paye', 12)->default(0)->after('tax_deductions');
            $table->decimal('nssf', 12)->default(0)->after('paye');
            $table->decimal('shif', 12)->default(0)->after('nssf');
            $table->decimal('housing_levy', 12)->default(0)->after('shif');
            $table->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paid_by');
            $table->dropColumn(['overtime_hours', 'paye', 'nssf', 'shif', 'housing_levy']);
        });

        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropColumn(['job_title', 'id_number', 'kra_pin', 'nssf_number', 'shif_number', 'end_date', 'notes']);
        });
    }
};
