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
        Schema::table('email_outbox', function (Blueprint $table) {
            $table->foreign(['template_id'], 'fk_outbox_tpl')->references(['id'])->on('email_templates')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['user_id'], 'fk_outbox_user')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_outbox', function (Blueprint $table) {
            $table->dropForeign('fk_outbox_tpl');
            $table->dropForeign('fk_outbox_user');
        });
    }
};
