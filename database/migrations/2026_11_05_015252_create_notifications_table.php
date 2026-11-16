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
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('type', 64);
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('data_json')->nullable();
            $table->dateTime('read_at', 3)->nullable();
            $table->dateTime('created_at', 3)->useCurrent();

            $table->index(['user_id', 'read_at', 'created_at'], 'idx_notifications_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
