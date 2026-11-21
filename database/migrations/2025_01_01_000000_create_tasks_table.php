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
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('task_type_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable()->index('tasks_shift_id_foreign');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('stay_id')->nullable()->index('tasks_stay_id_foreign');
            $table->unsignedBigInteger('facility_id')->nullable()->index('tasks_facility_id_foreign');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'verified', 'cancelled', 'on_hold'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->integer('estimated_minutes')->nullable();
            $table->integer('actual_minutes')->nullable();
            $table->dateTime('due_date');
            $table->dateTime('scheduled_start')->nullable();
            $table->dateTime('scheduled_end')->nullable();
            $table->unsignedBigInteger('created_by')->index('tasks_created_by_foreign');
            $table->unsignedBigInteger('verified_by')->nullable()->index('tasks_verified_by_foreign');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->decimal('estimated_cost', 10)->nullable();
            $table->decimal('actual_cost', 10)->nullable();
            $table->json('checklist')->nullable()->comment('JSON array of checklist items with completion status');
            $table->text('notes')->nullable();
            $table->text('verification_notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_pattern', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->unsignedBigInteger('parent_task_id')->nullable()->index('tasks_parent_task_id_foreign');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to', 'status']);
            $table->index(['created_at', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['task_type_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
