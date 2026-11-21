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
        Schema::create('rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('room_number')->unique();
            $table->unsignedBigInteger('room_type_id')->index('rooms_room_type_id_foreign');
            $table->enum('status', ['available', 'occupied', 'cleaning', 'maintenance', 'out_of_order', 'reserved'])->default('available');
            $table->integer('floor');
            $table->string('wing')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
            $table->decimal('adult_price', 12)->nullable()->default(0);
            $table->decimal('child_price', 12)->nullable()->default(0);

            $table->index(['status', 'room_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
