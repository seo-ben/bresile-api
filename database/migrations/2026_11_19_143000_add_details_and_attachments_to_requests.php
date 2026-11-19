<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('requests')) {
            Schema::table('requests', function (Blueprint $table) {
                if (! Schema::hasColumn('requests', 'subject')) {
                    $table->string('subject', 190)->nullable()->after('type');
                }
                if (! Schema::hasColumn('requests', 'message')) {
                    $table->text('message')->nullable()->after('subject');
                }
                if (! Schema::hasColumn('requests', 'attachment_path')) {
                    $table->string('attachment_path', 255)->nullable()->after('message');
                }
                if (! Schema::hasColumn('requests', 'attachment_name')) {
                    $table->string('attachment_name', 190)->nullable()->after('attachment_path');
                }
                if (! Schema::hasColumn('requests', 'admin_attachment_path')) {
                    $table->string('admin_attachment_path', 255)->nullable()->after('decision_note');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('requests')) {
            Schema::table('requests', function (Blueprint $table) {
                $cols = [];
                foreach (['subject', 'message', 'attachment_path', 'attachment_name', 'admin_attachment_path'] as $col) {
                    if (Schema::hasColumn('requests', $col)) {
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
