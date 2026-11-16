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
        Schema::create('level_offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('level_id');
            $table->enum('study_mode', ['remote_home_country', 'in_person_brazil']);
            $table->boolean('is_available')->default(false);
            $table->boolean('allow_requests')->default(true);
            $table->text('conditions_text')->nullable();
            $table->unsignedBigInteger('conditions_form_id')->nullable()->index('fk_offers_form');
            $table->unsignedBigInteger('prerequisite_level_id')->nullable()->index('fk_offers_prereq');
            $table->enum('prerequisite_study_mode', ['remote_home_country', 'in_person_brazil'])->nullable();
            $table->decimal('base_price', 12);
            $table->char('currency', 3);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->unique(['level_id', 'study_mode'], 'uq_offer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_offers');
    }
};
