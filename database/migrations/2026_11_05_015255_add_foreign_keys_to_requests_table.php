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
        Schema::table('requests', function (Blueprint $table) {
            $table->foreign(['enrollment_id'], 'fk_requests_enrollment')->references(['id'])->on('enrollments')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['from_city_id'], 'fk_requests_from_city')->references(['id'])->on('brazil_cities')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['level_id'], 'fk_requests_level')->references(['id'])->on('levels')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['granted_offer_id'], 'fk_requests_offer')->references(['id'])->on('level_offers')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['resolved_by'], 'fk_requests_resolver')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['student_id'], 'fk_requests_student')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['submission_id'], 'fk_requests_submission')->references(['id'])->on('form_submissions')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['to_city_id'], 'fk_requests_to_city')->references(['id'])->on('brazil_cities')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign('fk_requests_enrollment');
            $table->dropForeign('fk_requests_from_city');
            $table->dropForeign('fk_requests_level');
            $table->dropForeign('fk_requests_offer');
            $table->dropForeign('fk_requests_resolver');
            $table->dropForeign('fk_requests_student');
            $table->dropForeign('fk_requests_submission');
            $table->dropForeign('fk_requests_to_city');
        });
    }
};
