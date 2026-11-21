<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                if (! Schema::hasColumn('enrollments', 'certificate_code')) {
                    $table->string('certificate_code', 40)->nullable()->unique()->after('status_reason');
                }
                if (! Schema::hasColumn('enrollments', 'certificate_issued_at')) {
                    $table->timestamp('certificate_issued_at')->nullable()->after('certificate_code');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $cols = [];
                foreach (['certificate_code', 'certificate_issued_at'] as $col) {
                    if (Schema::hasColumn('enrollments', $col)) {
                        $cols[] = $col;
                    }
                }
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
