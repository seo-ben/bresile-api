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
        Schema::create('class_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('level_id');
            $table->enum('study_mode', ['remote_home_country', 'in_person_brazil']);
            $table->unsignedBigInteger('brazil_city_id')->nullable()->index('fk_groups_city');
            $table->enum('rhythm', ['day', 'evening'])->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable()->index('fk_groups_teacher');
            $table->string('name', 160);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at', 3)->useCurrent();

            $table->index(['level_id', 'study_mode', 'brazil_city_id', 'rhythm'], 'idx_groups_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};
