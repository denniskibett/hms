<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Booking requests from the public website. They are not bookings: the
 * front desk converts a request into a booking (choosing the rooms) or
 * declines it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->unsignedTinyInteger('adults');
            $table->unsignedTinyInteger('children')->default(0);
            $table->unsignedTinyInteger('rooms')->default(1);
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('meal_plan', 2)->default('BB');
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'converted', 'declined'])->default('new');
            $table->foreignId('stay_id')->nullable()->constrained()->nullOnDelete();
            $table->string('decline_reason')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
