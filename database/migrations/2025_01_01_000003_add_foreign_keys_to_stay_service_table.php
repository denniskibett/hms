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
        Schema::table('stay_service', function (Blueprint $table) {
            $table->foreign(['service_id'], 'fk_stay_service_service')->references(['id'])->on('services')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['stay_id'], 'fk_stay_service_stay')->references(['id'])->on('stays')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stay_service', function (Blueprint $table) {
            $table->dropForeign('fk_stay_service_service');
            $table->dropForeign('fk_stay_service_stay');
        });
    }
};
