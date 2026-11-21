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
        Schema::create('task_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code')->unique()->comment('Unique code for task type');
            $table->unsignedBigInteger('department_id')->nullable()->index('task_types_department_id_foreign');
            $table->enum('category', ['cleaning', 'maintenance', 'kitchen', 'reception', 'admin', 'hr', 'other'])->default('cleaning');
            $table->text('description')->nullable();
            $table->json('default_checklist')->nullable()->comment('JSON array of default checklist items');
            $table->integer('default_estimated_minutes')->nullable();
            $table->decimal('default_estimated_cost', 10)->nullable();
            $table->boolean('requires_room')->default(false)->comment('Whether this task type requires a room assignment');
            $table->boolean('requires_inventory')->default(false)->comment('Whether this task type requires inventory items');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_types');
    }
};
