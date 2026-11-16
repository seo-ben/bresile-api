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
        Schema::create('offer_payment_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('offer_id');
            $table->string('code', 40);
            $table->string('label', 120);
            $table->unsignedTinyInteger('installments_count')->default(1);
            $table->unsignedTinyInteger('interval_months')->default(1);
            $table->decimal('total_amount', 12);
            $table->boolean('is_active')->default(true);

            $table->unique(['offer_id', 'code'], 'uq_plan_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_payment_plans');
    }
};
