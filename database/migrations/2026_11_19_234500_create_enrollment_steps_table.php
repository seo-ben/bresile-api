<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('enrollment_steps')) {
            Schema::create('enrollment_steps', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('position')->default(1);
                $table->string('title', 160);
                $table->string('subtitle', 255)->nullable();
                $table->string('icon', 60)->default('file-signature');
                $table->string('accent_color', 30)->default('emerald');
                $table->json('branches_json')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_steps');
    }
};
