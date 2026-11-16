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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign(['bank_account_id'], 'fk_payments_bank')->references(['id'])->on('bank_accounts')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['confirmed_by'], 'fk_payments_confirmer')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['enrollment_id'], 'fk_payments_enrollment')->references(['id'])->on('enrollments')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['student_id'], 'fk_payments_student')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['confirmation_submission_id'], 'fk_payments_submission')->references(['id'])->on('form_submissions')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('fk_payments_bank');
            $table->dropForeign('fk_payments_confirmer');
            $table->dropForeign('fk_payments_enrollment');
            $table->dropForeign('fk_payments_student');
            $table->dropForeign('fk_payments_submission');
        });
    }
};
