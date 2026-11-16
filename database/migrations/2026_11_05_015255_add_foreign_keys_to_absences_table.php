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
        Schema::table('absences', function (Blueprint $table) {
            $table->foreign(['enrollment_id'], 'fk_absences_enrollment')->references(['id'])->on('enrollments')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['recorded_by'], 'fk_absences_recorder')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['justification_submission_id'], 'fk_absences_submission')->references(['id'])->on('form_submissions')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropForeign('fk_absences_enrollment');
            $table->dropForeign('fk_absences_recorder');
            $table->dropForeign('fk_absences_submission');
        });
    }
};
