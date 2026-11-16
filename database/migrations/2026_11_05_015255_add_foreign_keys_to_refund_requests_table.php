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
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->foreign(['enrollment_id'], 'fk_refund_enrollment')->references(['id'])->on('enrollments')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['refund_policy_id'], 'fk_refund_policy')->references(['id'])->on('refund_policies')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['reviewed_by'], 'fk_refund_reviewer')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['student_id'], 'fk_refund_student')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['submission_id'], 'fk_refund_submission')->references(['id'])->on('form_submissions')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['visa_application_id'], 'fk_refund_visa')->references(['id'])->on('visa_applications')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropForeign('fk_refund_enrollment');
            $table->dropForeign('fk_refund_policy');
            $table->dropForeign('fk_refund_reviewer');
            $table->dropForeign('fk_refund_student');
            $table->dropForeign('fk_refund_submission');
            $table->dropForeign('fk_refund_visa');
        });
    }
};
