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
        Schema::table('request_message_files', function (Blueprint $table) {
            $table->foreign(['file_id'], 'fk_msgfiles_file')->references(['id'])->on('files')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['message_id'], 'fk_msgfiles_message')->references(['id'])->on('request_messages')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_message_files', function (Blueprint $table) {
            $table->dropForeign('fk_msgfiles_file');
            $table->dropForeign('fk_msgfiles_message');
        });
    }
};
