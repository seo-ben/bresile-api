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
        Schema::table('exams', function (Blueprint $table) {
            $table->foreign(['created_by'], 'fk_exams_creator')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['class_group_id'], 'fk_exams_group')->references(['id'])->on('class_groups')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['level_id'], 'fk_exams_level')->references(['id'])->on('levels')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign('fk_exams_creator');
            $table->dropForeign('fk_exams_group');
            $table->dropForeign('fk_exams_level');
        });
    }
};
