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
        Schema::table('form_answer_files', function (Blueprint $table) {
            $table->foreign(['answer_id'], 'fk_answer_files_answer')->references(['id'])->on('form_answers')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['file_id'], 'fk_answer_files_file')->references(['id'])->on('files')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_answer_files', function (Blueprint $table) {
            $table->dropForeign('fk_answer_files_answer');
            $table->dropForeign('fk_answer_files_file');
        });
    }
};
