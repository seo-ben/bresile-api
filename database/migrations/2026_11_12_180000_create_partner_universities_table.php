<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('partner_universities')) {
            Schema::create('partner_universities', function (Blueprint $table) {
                $table->id();
                $table->string('sigle', 50);
                $table->string('name', 255);
                $table->string('full_name', 255);
                $table->string('city', 150);
                $table->string('state', 150)->nullable();
                $table->string('rank_badge', 150)->nullable();
                $table->text('highlight')->nullable();
                $table->string('badge_gradient', 255)->nullable();
                $table->string('website_url', 255)->nullable();
                $table->integer('position')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_universities');
    }
};

