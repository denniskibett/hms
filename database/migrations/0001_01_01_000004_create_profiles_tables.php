<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::create('guest_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('id_type', ['passport', 'national_id', 'driving_license', 'other']);
            $table->string('id_number');
            $table->string('nationality')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
            
            $table->unique(['id_type', 'id_number']);
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained();
            $table->decimal('salary', 12, 2)->default(0);
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

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('guest_profiles');
        Schema::dropIfExists('departments');
    }
};