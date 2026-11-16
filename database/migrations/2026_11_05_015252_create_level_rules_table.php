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
        Schema::create('level_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('level_id')->primary();
            $table->unsignedInteger('max_absence_minutes');
            $table->enum('absence_period', ['per_month', 'per_level']);
            $table->boolean('count_justified_absences');
            $table->enum('absence_failure_refund', ['full_refund', 'partial_refund', 'no_refund']);
            $table->decimal('absence_failure_refund_pct', 5)->nullable();
            $table->decimal('grade_scale_max', 5)->default(20);
            $table->decimal('passing_average', 5);
            $table->enum('below_average_action', ['retake_exam', 'new_payment', 'withdrawal']);
            $table->unsignedSmallInteger('payment_grace_days')->default(0);
            $table->unsignedBigInteger('updated_by')->nullable()->index('fk_rules_user');
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_rules');
    }
};
