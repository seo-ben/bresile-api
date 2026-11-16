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
        Schema::table('level_rules', function (Blueprint $table) {
            $table->foreign(['level_id'], 'fk_rules_level')->references(['id'])->on('levels')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['updated_by'], 'fk_rules_user')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('level_rules', function (Blueprint $table) {
            $table->dropForeign('fk_rules_level');
            $table->dropForeign('fk_rules_user');
        });
    }
};
