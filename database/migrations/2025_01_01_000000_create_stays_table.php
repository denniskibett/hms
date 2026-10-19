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
        Schema::create('stays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('guest_id')->index('stays_guest_id_foreign');
            $table->enum('status', ['booked', 'checked_in', 'checked_out', 'cancelled', 'reserved'])->default('reserved');
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->text('special_requests')->nullable();
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->unsignedBigInteger('created_by')->index('stays_created_by_foreign');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'arrival_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stays');
    }
};
