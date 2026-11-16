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
        Schema::table('exam_results', function (Blueprint $table) {
            $table->foreign(['enrollment_id'], 'fk_results_enrollment')->references(['id'])->on('enrollments')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['exam_id'], 'fk_results_exam')->references(['id'])->on('exams')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['recorded_by'], 'fk_results_recorder')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropForeign('fk_results_enrollment');
            $table->dropForeign('fk_results_exam');
            $table->dropForeign('fk_results_recorder');
        });
    }
};
