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
        DB::statement("CREATE VIEW `v_enrollment_averages` AS select `en`.`id` AS `enrollment_id`,round(sum(`er`.`score` / `ex`.`max_score` * `lr`.`grade_scale_max` * `ex`.`weight`) / nullif(sum(`ex`.`weight`),0),2) AS `average_score` from (((`portugais_bresilien`.`enrollments` `en` join `portugais_bresilien`.`level_rules` `lr` on(`lr`.`level_id` = `en`.`level_id`)) join `portugais_bresilien`.`exam_results` `er` on(`er`.`enrollment_id` = `en`.`id` and `er`.`score` is not null)) join `portugais_bresilien`.`exams` `ex` on(`ex`.`id` = `er`.`exam_id`)) group by `en`.`id`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_enrollment_averages`");
    }
};
