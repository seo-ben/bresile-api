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
        Schema::table('visa_documents', function (Blueprint $table) {
            $table->foreign(['visa_application_id'], 'fk_visa_docs_application')->references(['id'])->on('visa_applications')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['file_id'], 'fk_visa_docs_file')->references(['id'])->on('files')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['issued_by'], 'fk_visa_docs_issuer')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_documents', function (Blueprint $table) {
            $table->dropForeign('fk_visa_docs_application');
            $table->dropForeign('fk_visa_docs_file');
            $table->dropForeign('fk_visa_docs_issuer');
        });
    }
};
