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
        Schema::table('enrollment_installments', function (Blueprint $table) {
            $table->foreign(['enrollment_id'], 'fk_inst_enrollment')->references(['id'])->on('enrollments')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['payment_id'], 'fk_inst_payment')->references(['id'])->on('payments')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_installments', function (Blueprint $table) {
            $table->dropForeign('fk_inst_enrollment');
            $table->dropForeign('fk_inst_payment');
        });
    }
};
