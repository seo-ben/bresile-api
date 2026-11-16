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
        Schema::create('sender_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('label', 120);
            $table->string('email_address', 190)->unique('uq_sender_email');
            $table->enum('purpose', ['bank_transfer_info', 'notifications', 'support', 'general'])->default('general');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->index(['purpose', 'is_active'], 'idx_sender_purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sender_emails');
    }
};
