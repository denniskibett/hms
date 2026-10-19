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
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shift_id')->index('shift_assignments_shift_id_foreign');
            $table->date('date');
            $table->enum('status', ['scheduled', 'completed', 'absent', 'on_leave'])->default('scheduled');
            $table->decimal('overtime_hours', 5)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'status']);
            $table->unique(['user_id', 'shift_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
    }
};
