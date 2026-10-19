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
        Schema::create('stay_service', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stay_id')->index('fk_stay_service_stay');
            $table->unsignedBigInteger('service_id')->index('fk_stay_service_service');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stay_service');
    }
};
