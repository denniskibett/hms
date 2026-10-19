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
        Schema::table('room_allocations', function (Blueprint $table) {
            $table->foreign(['room_id'])->references(['id'])->on('rooms')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['stay_id'])->references(['id'])->on('stays')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_allocations', function (Blueprint $table) {
            $table->dropForeign('room_allocations_room_id_foreign');
            $table->dropForeign('room_allocations_stay_id_foreign');
        });
    }
};
