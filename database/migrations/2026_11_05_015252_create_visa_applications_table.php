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
        Schema::create('visa_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->index('idx_visa_student');
            $table->unsignedBigInteger('enrollment_id')->nullable()->index('fk_visa_enrollment');
            $table->unsignedBigInteger('submission_id')->nullable()->index('fk_visa_submission');
            $table->enum('status', ['draft', 'submitted', 'in_review', 'more_info_needed', 'documents_issued', 'closed'])->default('draft');
            $table->enum('visa_outcome', ['pending', 'granted', 'refused'])->default('pending');
            $table->dateTime('outcome_reported_at', 3)->nullable();
            $table->dateTime('submitted_at', 3)->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable()->index('fk_visa_reviewer');
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->index(['status', 'visa_outcome'], 'idx_visa_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_applications');
    }
};
