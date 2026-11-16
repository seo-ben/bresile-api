<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE VIEW `v_students_by_city_rhythm` AS select `lv`.`code` AS `level_code`,`bc`.`name` AS `city_name`,`en`.`rhythm` AS `rhythm`,count(0) AS `students_count` from ((`portugais_bresilien`.`enrollments` `en` join `portugais_bresilien`.`levels` `lv` on(`lv`.`id` = `en`.`level_id`)) left join `portugais_bresilien`.`brazil_cities` `bc` on(`bc`.`id` = `en`.`brazil_city_id`)) where `en`.`status` = 'active' group by `lv`.`code`,`bc`.`name`,`en`.`rhythm`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_students_by_city_rhythm`");
    }
};
