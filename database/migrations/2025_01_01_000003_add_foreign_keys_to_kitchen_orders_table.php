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
        Schema::table('kitchen_orders', function (Blueprint $table) {
            $table->foreign(['placed_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['stay_id'])->references(['id'])->on('stays')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_orders', function (Blueprint $table) {
            $table->dropForeign('kitchen_orders_placed_by_foreign');
            $table->dropForeign('kitchen_orders_stay_id_foreign');
        });
    }
};
