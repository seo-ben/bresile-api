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
        Schema::create('email_outbox', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('template_id')->nullable()->index('fk_outbox_tpl');
            $table->unsignedBigInteger('user_id')->nullable()->index('idx_outbox_user');
            $table->string('from_email', 190)->nullable();
            $table->string('to_email', 190);
            $table->string('subject');
            $table->mediumText('body_html');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->string('error_message', 500)->nullable();
            $table->dateTime('queued_at', 3)->useCurrent();
            $table->dateTime('sent_at', 3)->nullable();

            $table->index(['status', 'queued_at'], 'idx_outbox_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_outbox');
    }
};
