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
        Schema::create('absences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enrollment_id');
            $table->date('absence_date');
            $table->unsignedInteger('minutes_absent');
            $table->boolean('is_justified')->default(false);
            $table->string('justification_note', 500)->nullable();
            $table->unsignedBigInteger('justification_submission_id')->nullable()->index('fk_absences_submission');
            $table->unsignedBigInteger('recorded_by')->nullable()->index('fk_absences_recorder');
            $table->dateTime('created_at', 3)->useCurrent();

            $table->index(['enrollment_id', 'absence_date'], 'idx_absences_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
