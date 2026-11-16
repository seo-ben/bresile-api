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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('form_id');
            $table->string('field_key', 64);
            $table->string('label');
            $table->string('help_text', 500)->nullable();
            $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'email', 'phone', 'url', 'select', 'multiselect', 'radio', 'checkbox', 'file', 'country', 'brazil_city']);
            $table->enum('system_key', ['first_name', 'last_name', 'phone', 'country_code', 'study_mode', 'rhythm', 'brazil_city_id'])->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_editable_after_submit')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('default_value', 500)->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('visible_if')->nullable();
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();

            $table->index(['form_id', 'is_active', 'position'], 'idx_fields_order');
            $table->unique(['form_id', 'field_key'], 'uq_field_key');
            $table->unique(['form_id', 'system_key'], 'uq_field_system_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
