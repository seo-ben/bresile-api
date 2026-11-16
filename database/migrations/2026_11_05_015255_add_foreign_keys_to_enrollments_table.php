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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreign(['brazil_city_id'], 'fk_enroll_city')->references(['id'])->on('brazil_cities')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['class_group_id'], 'fk_enroll_group')->references(['id'])->on('class_groups')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['level_id'], 'fk_enroll_level')->references(['id'])->on('levels')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['offer_id'], 'fk_enroll_offer')->references(['id'])->on('level_offers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['payment_plan_id'], 'fk_enroll_plan')->references(['id'])->on('offer_payment_plans')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['previous_enrollment_id'], 'fk_enroll_prev')->references(['id'])->on('enrollments')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['student_id'], 'fk_enroll_student')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign('fk_enroll_city');
            $table->dropForeign('fk_enroll_group');
            $table->dropForeign('fk_enroll_level');
            $table->dropForeign('fk_enroll_offer');
            $table->dropForeign('fk_enroll_plan');
            $table->dropForeign('fk_enroll_prev');
            $table->dropForeign('fk_enroll_student');
        });
    }
};
