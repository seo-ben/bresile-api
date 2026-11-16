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
        Schema::table('terms_acceptances', function (Blueprint $table) {
            $table->foreign(['cms_content_id'], 'fk_terms_content')->references(['id'])->on('cms_contents')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['user_id'], 'fk_terms_user')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terms_acceptances', function (Blueprint $table) {
            $table->dropForeign('fk_terms_content');
            $table->dropForeign('fk_terms_user');
        });
    }
};
