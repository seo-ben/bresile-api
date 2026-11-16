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
        Schema::table('visa_applications', function (Blueprint $table) {
            $table->foreign(['enrollment_id'], 'fk_visa_enrollment')->references(['id'])->on('enrollments')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['reviewed_by'], 'fk_visa_reviewer')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['student_id'], 'fk_visa_student')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['submission_id'], 'fk_visa_submission')->references(['id'])->on('form_submissions')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            $table->dropForeign('fk_visa_enrollment');
            $table->dropForeign('fk_visa_reviewer');
            $table->dropForeign('fk_visa_student');
            $table->dropForeign('fk_visa_submission');
        });
    }
};
