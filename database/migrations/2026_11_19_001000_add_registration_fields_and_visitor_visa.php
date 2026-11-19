<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_profiles') && ! Schema::hasColumn('user_profiles', 'birth_date')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->date('birth_date')->nullable()->after('last_name');
            });
        }

        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                if (! Schema::hasColumn('enrollments', 'study_purpose')) {
                    $table->string('study_purpose', 120)->nullable()->after('country_residence');
                }
                if (! Schema::hasColumn('enrollments', 'visa_type')) {
                    $table->string('visa_type', 60)->nullable()->after('study_purpose');
                }
                if (! Schema::hasColumn('enrollments', 'previous_level_certificate_path')) {
                    $table->string('previous_level_certificate_path', 255)->nullable()->after('visa_type');
                }
            });
        }

        // Extend enum study_mode on level_offers, class_groups, and student_requests (if present) for MySQL
        if (DB::getDriverName() === 'mysql') {
            if (Schema::hasTable('level_offers') && Schema::hasColumn('level_offers', 'study_mode')) {
                DB::statement("ALTER TABLE `level_offers` MODIFY COLUMN `study_mode` ENUM('remote_home_country', 'in_person_brazil', 'visitor_visa_brazil') NOT NULL");
            }
            if (Schema::hasTable('class_groups') && Schema::hasColumn('class_groups', 'study_mode')) {
                DB::statement("ALTER TABLE `class_groups` MODIFY COLUMN `study_mode` ENUM('remote_home_country', 'in_person_brazil', 'visitor_visa_brazil') NOT NULL");
            }
            if (Schema::hasTable('student_requests') && Schema::hasColumn('student_requests', 'requested_study_mode')) {
                try {
                    DB::statement("ALTER TABLE `student_requests` MODIFY COLUMN `requested_study_mode` ENUM('remote_home_country', 'in_person_brazil', 'visitor_visa_brazil') NULL");
                } catch (\Throwable) {
                    // Column might already be varchar
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_profiles') && Schema::hasColumn('user_profiles', 'birth_date')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropColumn('birth_date');
            });
        }

        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $cols = [];
                foreach (['study_purpose', 'visa_type', 'previous_level_certificate_path'] as $col) {
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
