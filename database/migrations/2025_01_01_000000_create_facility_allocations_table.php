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
        Schema::create('facility_allocations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stay_id')->index('facility_allocations_stay_id_foreign');
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('package_id')->nullable()->index('facility_allocations_package_id_foreign');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('rate_applied', 12);
            $table->enum('status', ['booked', 'confirmed', 'in_use', 'completed', 'cancelled'])->default('booked');
            $table->timestamps();

            $table->index(['facility_id', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_allocations');
    }
};
