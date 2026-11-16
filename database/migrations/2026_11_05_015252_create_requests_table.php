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
        Schema::create('requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->index('idx_requests_student');
            $table->enum('type', ['a1_brazil', 'offer_access', 'city_change', 'other']);
            $table->unsignedBigInteger('level_id')->nullable()->index('fk_requests_level');
            $table->enum('requested_study_mode', ['remote_home_country', 'in_person_brazil'])->nullable();
            $table->unsignedBigInteger('enrollment_id')->nullable()->index('fk_requests_enrollment');
            $table->unsignedBigInteger('from_city_id')->nullable()->index('fk_requests_from_city');
            $table->unsignedBigInteger('to_city_id')->nullable()->index('fk_requests_to_city');
            $table->unsignedBigInteger('submission_id')->nullable()->index('fk_requests_submission');
            $table->enum('status', ['open', 'awaiting_student', 'approved', 'rejected', 'cancelled'])->default('open');
            $table->unsignedBigInteger('granted_offer_id')->nullable()->index('fk_requests_offer');
            $table->text('decision_note')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable()->index('fk_requests_resolver');
            $table->dateTime('resolved_at', 3)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->index(['status', 'type', 'created_at'], 'idx_requests_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
