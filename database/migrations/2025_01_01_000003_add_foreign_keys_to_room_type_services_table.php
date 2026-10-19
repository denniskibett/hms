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
        Schema::table('room_type_services', function (Blueprint $table) {
            $table->foreign(['room_type_id'])->references(['id'])->on('room_types')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_type_services', function (Blueprint $table) {
            $table->dropForeign('room_type_services_room_type_id_foreign');
        });
    }
};
