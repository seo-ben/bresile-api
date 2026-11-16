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
        DB::statement("CREATE VIEW `v_absence_level_totals` AS select `a`.`enrollment_id` AS `enrollment_id`,sum(case when `a`.`is_justified` = 1 and `lr`.`count_justified_absences` = 0 then 0 else `a`.`minutes_absent` end) AS `counted_minutes` from ((`portugais_bresilien`.`absences` `a` join `portugais_bresilien`.`enrollments` `en` on(`en`.`id` = `a`.`enrollment_id`)) join `portugais_bresilien`.`level_rules` `lr` on(`lr`.`level_id` = `en`.`level_id`)) group by `a`.`enrollment_id`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_absence_level_totals`");
    }
};
