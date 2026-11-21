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
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('staff_profiles_user_id_foreign');
            $table->unsignedBigInteger('department_id')->nullable()->index('staff_profiles_department_id_foreign');
            $table->decimal('salary', 12)->default(0);
            $table->date('hire_date');
            $table->unsignedInteger('contract_period')->default(3);
            $table->enum('employment_status', ['internship', 'probation', 'permanent', 'contract', 'terminated']);
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('account_number')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
