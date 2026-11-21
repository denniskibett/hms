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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign(['assigned_to'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['department_id'])->references(['id'])->on('departments')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['facility_id'])->references(['id'])->on('facilities')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['parent_task_id'])->references(['id'])->on('tasks')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['room_id'])->references(['id'])->on('rooms')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['shift_id'])->references(['id'])->on('shifts')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['stay_id'])->references(['id'])->on('stays')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['task_type_id'])->references(['id'])->on('task_types')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['verified_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_assigned_to_foreign');
            $table->dropForeign('tasks_created_by_foreign');
            $table->dropForeign('tasks_department_id_foreign');
            $table->dropForeign('tasks_facility_id_foreign');
            $table->dropForeign('tasks_parent_task_id_foreign');
            $table->dropForeign('tasks_room_id_foreign');
            $table->dropForeign('tasks_shift_id_foreign');
            $table->dropForeign('tasks_stay_id_foreign');
            $table->dropForeign('tasks_task_type_id_foreign');
            $table->dropForeign('tasks_verified_by_foreign');
        });
    }
};
