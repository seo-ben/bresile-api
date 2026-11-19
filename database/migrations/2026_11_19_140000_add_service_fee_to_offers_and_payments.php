<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('level_offers') && ! Schema::hasColumn('level_offers', 'service_fee')) {
            Schema::table('level_offers', function (Blueprint $table) {
                $table->decimal('service_fee', 12, 2)->default(150.00)->after('study_mode');
            });
        }

        if (DB::getDriverName() === 'mysql' && Schema::hasTable('payments') && Schema::hasColumn('payments', 'purpose')) {
            DB::statement("ALTER TABLE `payments` MODIFY COLUMN `purpose` ENUM('service_fee', 'level_fee', 'final_settlement') NOT NULL DEFAULT 'level_fee'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('level_offers') && Schema::hasColumn('level_offers', 'service_fee')) {
            Schema::table('level_offers', function (Blueprint $table) {
                $table->dropColumn('service_fee');
            });
        }
    }
};
