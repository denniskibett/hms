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
        Schema::table('task_items', function (Blueprint $table) {
            $table->foreign(['inventory_item_id'])->references(['id'])->on('inventory_items')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['task_id'])->references(['id'])->on('tasks')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_items', function (Blueprint $table) {
            $table->dropForeign('task_items_inventory_item_id_foreign');
            $table->dropForeign('task_items_task_id_foreign');
        });
    }
};
