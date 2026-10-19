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
        Schema::table('facility_allocations', function (Blueprint $table) {
            $table->foreign(['facility_id'])->references(['id'])->on('facilities')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['package_id'])->references(['id'])->on('facility_packages')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['stay_id'])->references(['id'])->on('stays')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_allocations', function (Blueprint $table) {
            $table->dropForeign('facility_allocations_facility_id_foreign');
            $table->dropForeign('facility_allocations_package_id_foreign');
            $table->dropForeign('facility_allocations_stay_id_foreign');
        });
    }
};
