<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['booked', 'checked_in', 'checked_out', 'cancelled'])->default('booked');
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->text('special_requests')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
            $table->softDeletes();    
            
            $table->index(['status', 'arrival_date']);
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('base_rate', 12, 2);
            $table->integer('capacity');
            $table->enum('bed_type', ['single', 'double', 'queen', 'king', 'twin']);
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->timestamps();
        });

        Schema::create('room_type_services', function (Blueprint $table) {
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('service_name');
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->primary(['room_type_id', 'service_name']);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->foreignId('room_type_id')->constrained();
            $table->enum('status', ['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order'])->default('available');
            $table->integer('floor');
            $table->string('wing')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'room_type_id']);
        });

        Schema::create('room_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained();
            $table->date('from_date');
            $table->date('to_date');
            $table->decimal('rate_applied', 12, 2);
            $table->timestamps();
            
            $table->index(['room_id', 'from_date', 'to_date']);
            $table->unique(['stay_id', 'room_id', 'from_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_allocations');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_type_services');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('stays');
    }
};