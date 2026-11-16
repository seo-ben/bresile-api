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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('level_id')->index('fk_enroll_level');
            $table->unsignedBigInteger('offer_id')->index('fk_enroll_offer');
            $table->unsignedBigInteger('payment_plan_id')->nullable()->index('fk_enroll_plan');
            $table->unsignedBigInteger('brazil_city_id')->nullable();
            $table->enum('rhythm', ['day', 'evening'])->nullable();
            $table->unsignedBigInteger('class_group_id')->nullable()->index('idx_enroll_group');
            $table->unsignedBigInteger('previous_enrollment_id')->nullable()->index('fk_enroll_prev');
            $table->unsignedTinyInteger('attempt_no')->default(1);
            $table->enum('status', ['pending_payment', 'active', 'excluded_unpaid', 'failed_absence', 'not_validated', 'validated', 'withdrawn', 'completed'])->default('pending_payment')->index('idx_enroll_status');
            $table->string('status_reason')->nullable();
            $table->dateTime('enrolled_at', 3)->useCurrent();
            $table->dateTime('started_at', 3)->nullable();
            $table->dateTime('ended_at', 3)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->index(['brazil_city_id', 'rhythm'], 'idx_enroll_city_rhythm');
            $table->index(['student_id', 'level_id'], 'idx_enroll_student_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
