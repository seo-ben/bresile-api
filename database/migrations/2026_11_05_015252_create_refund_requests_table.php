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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->index('idx_refund_student');
            $table->unsignedBigInteger('visa_application_id')->nullable()->index('fk_refund_visa');
            $table->unsignedBigInteger('enrollment_id')->nullable()->index('fk_refund_enrollment');
            $table->unsignedBigInteger('refund_policy_id')->index('fk_refund_policy');
            $table->enum('reason', ['visa_refusal', 'academic_failure', 'other']);
            $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'paid'])->default('draft');
            $table->decimal('total_paid_amount', 12);
            $table->decimal('refund_percent', 5);
            $table->decimal('refund_amount', 12);
            $table->char('currency', 3);
            $table->unsignedBigInteger('submission_id')->nullable()->index('fk_refund_submission');
            $table->unsignedBigInteger('reviewed_by')->nullable()->index('fk_refund_reviewer');
            $table->dateTime('reviewed_at', 3)->nullable();
            $table->dateTime('paid_at', 3)->nullable();
            $table->string('payout_reference', 128)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->index(['status', 'created_at'], 'idx_refund_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
