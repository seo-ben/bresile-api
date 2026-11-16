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
        Schema::create('brazil_cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 120);
            $table->char('state_code', 2);
            $table->boolean('is_available')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->index(['is_available', 'position'], 'idx_city_available');
            $table->unique(['name', 'state_code'], 'uq_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brazil_cities');
    }
};
