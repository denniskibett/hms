// database/migrations/xxxx_create_tasks_tables.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->comment('Unique code for task type');
            $table->foreignId('department_id')->nullable()->constrained()->comment('Default department for this task type');
            $table->enum('category', ['cleaning', 'maintenance', 'kitchen', 'reception', 'admin', 'hr', 'other'])->default('cleaning');
            $table->text('description')->nullable();
            $table->json('default_checklist')->nullable()->comment('JSON array of default checklist items');
            $table->integer('default_estimated_minutes')->nullable();
            $table->decimal('default_estimated_cost', 10, 2)->nullable();
            $table->boolean('requires_room')->default(false)->comment('Whether this task type requires a room assignment');
            $table->boolean('requires_inventory')->default(false)->comment('Whether this task type requires inventory items');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category', 'is_active']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_type_id')->constrained()->comment('Type of task');
            $table->string('title');
            $table->text('description')->nullable();
            
            // Task assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->comment('Staff assigned to task');
            $table->foreignId('department_id')->nullable()->constrained()->comment('Department responsible');
            $table->foreignId('shift_id')->nullable()->constrained()->comment('Shift for the task');
            
            // Task location (optional based on task type)
            $table->foreignId('room_id')->nullable()->constrained()->comment('Room for the task (if applicable)');
            $table->foreignId('stay_id')->nullable()->constrained()->comment('Stay related to task (if applicable)');
            $table->foreignId('facility_id')->nullable()->constrained()->comment('Facility for task (if applicable)');
            
            // Task status and priority
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'verified', 'cancelled', 'on_hold'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Time tracking
            $table->integer('estimated_minutes')->nullable();
            $table->integer('actual_minutes')->nullable();
            $table->dateTime('due_date');
            $table->dateTime('scheduled_start')->nullable();
            $table->dateTime('scheduled_end')->nullable();
            
            // Task tracking
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            // Cost tracking
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            
            // Additional details
            $table->json('checklist')->nullable()->comment('JSON array of checklist items with completion status');
            $table->text('notes')->nullable();
            $table->text('verification_notes')->nullable();
            
            // For recurring tasks
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_pattern', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->integer('recurrence_interval')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->comment('For recurring task series');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['task_type_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['created_at', 'status']);
            $table->index(['department_id', 'status']);
        });

        // Task items (inventory usage)
        Schema::create('task_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained();
            $table->decimal('quantity_used', 10, 2);
            $table->decimal('unit_cost_at_time', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['task_id', 'inventory_item_id']);
            $table->index(['inventory_item_id', 'created_at']);
        });

        // Task comments
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->text('content');
            $table->timestamps();
            
            $table->index(['task_id', 'created_at']);
        });

        // Task attachments
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_items');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_types');
    }
};