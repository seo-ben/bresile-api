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
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('enrollment_id')->nullable()->index('idx_payments_enrollment');
            $table->enum('purpose', ['level_fee', 'final_settlement'])->default('level_fee');
            $table->enum('method', ['card_visa', 'bank_transfer']);
            $table->decimal('amount', 12);
            $table->char('currency', 3);
            $table->enum('status', ['initiated', 'awaiting_confirmation', 'confirmed', 'failed', 'cancelled', 'refunded'])->default('initiated');
            $table->string('gateway_name', 50)->nullable();
            $table->string('gateway_reference', 128)->nullable();
            $table->string('student_payment_reference', 128)->nullable();
            $table->unsignedBigInteger('bank_account_id')->nullable()->index('fk_payments_bank');
            $table->unsignedBigInteger('confirmation_submission_id')->nullable()->index('fk_payments_submission');
            $table->dateTime('confirm_deadline_at', 3)->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable()->index('fk_payments_confirmer');
            $table->dateTime('confirmed_at', 3)->nullable();
            $table->string('failure_reason')->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->index(['method', 'status', 'confirm_deadline_at'], 'idx_payments_pending_bank');
            $table->index(['student_id', 'status'], 'idx_payments_student');
            $table->unique(['gateway_name', 'gateway_reference'], 'uq_payment_gateway_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
