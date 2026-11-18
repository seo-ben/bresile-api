<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('whatsapp_leads')) {
            Schema::create('whatsapp_leads', function (Blueprint $table) {
                $table->id();
                $table->string('dial_code', 12)->default('+221');
                $table->string('country_name', 80)->nullable();
                $table->string('phone_number', 40);
                $table->string('full_whatsapp', 60);
                $table->string('locale', 10)->default('fr');
                $table->string('status', 30)->default('new');
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_leads');
    }
};
