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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreign(['registration_submission_id'], 'fk_profiles_submission')->references(['id'])->on('form_submissions')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['user_id'], 'fk_profiles_user')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign('fk_profiles_submission');
            $table->dropForeign('fk_profiles_user');
        });
    }
};
