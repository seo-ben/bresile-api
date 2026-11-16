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
        Schema::create('enrollment_status_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('enrollment_id');
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable()->index('fk_history_user');
            $table->dateTime('changed_at', 3)->useCurrent();

            $table->index(['enrollment_id', 'changed_at'], 'idx_history_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_status_history');
    }
};
