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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 190)->unique('uq_users_email');
            $table->string('password_hash');
            $table->enum('role', ['student', 'teacher', 'admin'])->default('student');
            $table->enum('status', ['pending_verification', 'active', 'suspended'])->default('pending_verification');
            $table->char('preferred_locale', 5)->default('fr');
            $table->dateTime('email_verified_at', 3)->nullable();
            $table->dateTime('last_login_at', 3)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();
            $table->dateTime('deleted_at', 3)->nullable();

            $table->index(['role', 'status'], 'idx_users_role_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
