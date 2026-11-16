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
        Schema::create('forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('purpose', ['registration', 'payment_confirmation', 'a1_brazil_request', 'visa_application', 'refund_request', 'absence_justification', 'other']);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->dateTime('updated_at', 3)->useCurrentOnUpdate()->useCurrent();
            $table->string('active_purpose', 40)->nullable()->storedAs('if(`is_active` = 1 and `purpose` <> \'other\',cast(`purpose` as char charset utf8mb4),NULL)')->unique('uq_forms_one_active_per_purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
