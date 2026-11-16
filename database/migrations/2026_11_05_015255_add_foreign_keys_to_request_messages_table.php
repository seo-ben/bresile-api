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
        Schema::table('request_messages', function (Blueprint $table) {
            $table->foreign(['author_id'], 'fk_messages_author')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['request_id'], 'fk_messages_request')->references(['id'])->on('requests')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_messages', function (Blueprint $table) {
            $table->dropForeign('fk_messages_author');
            $table->dropForeign('fk_messages_request');
        });
    }
};
