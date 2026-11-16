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
        Schema::create('terms_acceptances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cms_content_id')->index('fk_terms_content');
            $table->unsignedInteger('content_version');
            $table->dateTime('accepted_at', 3)->useCurrent();
            $table->string('ip_address', 45)->nullable();

            $table->unique(['user_id', 'cms_content_id', 'content_version'], 'uq_terms_user_content_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms_acceptances');
    }
};
