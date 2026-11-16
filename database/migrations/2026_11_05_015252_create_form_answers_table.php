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
        Schema::create('form_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('field_id')->index('fk_answers_field');
            $table->string('field_key_snapshot', 64);
            $table->string('field_label_snapshot');
            $table->text('value_text')->nullable();
            $table->json('value_json')->nullable();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->unique(['submission_id', 'field_id'], 'uq_answer_per_field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_answers');
    }
};
