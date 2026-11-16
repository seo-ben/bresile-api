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
        Schema::table('level_offers', function (Blueprint $table) {
            $table->foreign(['conditions_form_id'], 'fk_offers_form')->references(['id'])->on('forms')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['level_id'], 'fk_offers_level')->references(['id'])->on('levels')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['prerequisite_level_id'], 'fk_offers_prereq')->references(['id'])->on('levels')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('level_offers', function (Blueprint $table) {
            $table->dropForeign('fk_offers_form');
            $table->dropForeign('fk_offers_level');
            $table->dropForeign('fk_offers_prereq');
        });
    }
};
