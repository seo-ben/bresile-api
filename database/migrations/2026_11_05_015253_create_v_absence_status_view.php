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
        DB::statement("CREATE OR REPLACE VIEW `v_absence_status` AS select `en`.`id` AS `enrollment_id`,`lr`.`max_absence_minutes` AS `max_absence_minutes`,`lr`.`absence_period` AS `absence_period`,coalesce(`lt`.`counted_minutes`,0) AS `level_minutes`,coalesce(`mx`.`worst_month_minutes`,0) AS `worst_month_minutes`,case when `lr`.`absence_period` = 'per_level' then coalesce(`lt`.`counted_minutes`,0) > `lr`.`max_absence_minutes` else coalesce(`mx`.`worst_month_minutes`,0) > `lr`.`max_absence_minutes` end AS `is_exceeded` from (((`enrollments` `en` join `level_rules` `lr` on(`lr`.`level_id` = `en`.`level_id`)) left join `v_absence_level_totals` `lt` on(`lt`.`enrollment_id` = `en`.`id`)) left join (select `v_absence_monthly_totals`.`enrollment_id` AS `enrollment_id`,max(`v_absence_monthly_totals`.`counted_minutes`) AS `worst_month_minutes` from `v_absence_monthly_totals` group by `v_absence_monthly_totals`.`enrollment_id`) `mx` on(`mx`.`enrollment_id` = `en`.`id`))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_absence_status`");
    }
};
