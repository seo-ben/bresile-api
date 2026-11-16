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
        Schema::create('exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('level_id')->index('fk_exams_level');
            $table->unsignedBigInteger('class_group_id')->nullable();
            $table->string('title', 200);
            $table->date('exam_date');
            $table->decimal('max_score', 5)->default(20);
            $table->decimal('weight', 5)->default(1);
            $table->unsignedBigInteger('created_by')->nullable()->index('fk_exams_creator');
            $table->dateTime('created_at', 3)->useCurrent();

            $table->index(['class_group_id', 'exam_date'], 'idx_exams_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
