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
        Schema::create('room_allocations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stay_id');
            $table->unsignedBigInteger('room_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('rate_applied', 12);
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->string('guest_notes')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'from_date', 'to_date']);
            $table->unique(['stay_id', 'room_id', 'from_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_allocations');
    }
};
