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
        Schema::create('level_results', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_id')->primary();
            $table->decimal('average_score', 5)->nullable();
            $table->decimal('passing_average_snapshot', 5);
            $table->enum('result', ['validated', 'not_validated', 'failed_absence']);
            $table->enum('follow_up', ['none', 'retake_exam', 'new_payment', 'withdrawal'])->default('none');
            $table->string('notes', 500)->nullable();
            $table->unsignedBigInteger('decided_by')->nullable()->index('fk_level_results_user');
            $table->dateTime('decided_at', 3)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_results');
    }
};
