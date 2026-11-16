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
        Schema::create('visa_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('visa_application_id')->index('idx_visa_docs_application');
            $table->string('title', 200);
            $table->unsignedBigInteger('file_id')->index('fk_visa_docs_file');
            $table->unsignedBigInteger('issued_by')->nullable()->index('fk_visa_docs_issuer');
            $table->dateTime('issued_at', 3)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_documents');
    }
};
