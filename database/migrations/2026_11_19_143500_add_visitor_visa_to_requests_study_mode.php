<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('requests') && Schema::hasColumn('requests', 'requested_study_mode')) {
            DB::statement("ALTER TABLE `requests` MODIFY COLUMN `requested_study_mode` ENUM('remote_home_country', 'in_person_brazil', 'visitor_visa_brazil') NULL");
        }
    }

    public function down(): void
    {
        // No-op
    }
};
