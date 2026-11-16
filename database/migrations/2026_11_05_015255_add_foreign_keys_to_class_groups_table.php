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
        Schema::table('class_groups', function (Blueprint $table) {
            $table->foreign(['brazil_city_id'], 'fk_groups_city')->references(['id'])->on('brazil_cities')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['level_id'], 'fk_groups_level')->references(['id'])->on('levels')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['teacher_id'], 'fk_groups_teacher')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropForeign('fk_groups_city');
            $table->dropForeign('fk_groups_level');
            $table->dropForeign('fk_groups_teacher');
        });
    }
};
