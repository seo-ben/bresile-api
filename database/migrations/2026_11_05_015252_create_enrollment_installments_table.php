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
        Schema::create('enrollment_installments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('payment_id')->nullable()->index('fk_inst_payment');
            $table->unsignedTinyInteger('installment_no');
            $table->date('due_date');
            $table->decimal('amount', 12);
            $table->char('currency', 3);
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->dateTime('paid_at', 3)->nullable();

            $table->index(['status', 'due_date'], 'idx_installments_due');
            $table->unique(['enrollment_id', 'installment_no'], 'uq_installment_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_installments');
    }
};
