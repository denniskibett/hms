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
        Schema::create('guest_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('guest_profiles_user_id_foreign');
            $table->enum('id_type', ['passport', 'national_id', 'driving_license', 'other']);
            $table->string('id_number');
            $table->string('nationality')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->unique(['id_type', 'id_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_profiles');
    }
};
