<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('capacity');
            $table->decimal('base_rate', 12, 2);
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->enum('status', ['available', 'unavailable', 'maintenance'])->default('available');
            $table->timestamps();
        });

        Schema::create('facility_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->integer('duration_hours');
            $table->text('inclusions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('facility_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained();
            $table->foreignId('package_id')->nullable()->constrained('facility_packages');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('rate_applied', 12, 2);
            $table->enum('status', ['booked', 'confirmed', 'in_use', 'completed', 'cancelled'])->default('booked');
            $table->timestamps();
            
            $table->index(['facility_id', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_allocations');
        Schema::dropIfExists('facility_packages');
        Schema::dropIfExists('facilities');
    }
};