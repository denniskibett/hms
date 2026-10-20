<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rates per room type and meal plan.
     *
     * per_room rows can be set per occupancy (adults = 1, 2, …); a row with
     * adults = NULL is the default for any occupancy.
     * per_person rows use adults = NULL; rate is per adult per night.
     */
    public function up(): void
    {
        Schema::create('room_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('room_type_id');
            $table->string('meal_plan', 2)->default('BB');
            $table->enum('basis', ['per_room', 'per_person'])->default('per_room');
            $table->unsignedTinyInteger('adults')->nullable()->comment('per_room: rate for exactly this many adults; NULL = any');
            $table->decimal('rate', 12, 2);
            $table->decimal('child_rate', 12, 2)->nullable()->comment('Per child per night; NULL = use the child % setting');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['room_type_id', 'meal_plan', 'adults']);
            $table->foreign('room_type_id')->references('id')->on('room_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_rates');
    }
};
