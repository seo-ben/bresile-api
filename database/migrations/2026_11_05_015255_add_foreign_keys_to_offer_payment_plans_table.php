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
        Schema::table('offer_payment_plans', function (Blueprint $table) {
            $table->foreign(['offer_id'], 'fk_plans_offer')->references(['id'])->on('level_offers')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_payment_plans', function (Blueprint $table) {
            $table->dropForeign('fk_plans_offer');
        });
    }
};
