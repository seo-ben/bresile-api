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
        DB::statement("CREATE OR REPLACE VIEW `v_teacher_roster` AS select `en`.`id` AS `enrollment_id`,`en`.`class_group_id` AS `class_group_id`,`cg`.`teacher_id` AS `teacher_id`,`en`.`student_id` AS `student_id`,`up`.`first_name` AS `first_name`,`up`.`last_name` AS `last_name`,`lv`.`code` AS `level_code`,`en`.`rhythm` AS `rhythm`,`bc`.`name` AS `city_name` from (((((`enrollments` `en` join `level_rules` `lr` on(`lr`.`level_id` = `en`.`level_id`)) join `levels` `lv` on(`lv`.`id` = `en`.`level_id`)) join `user_profiles` `up` on(`up`.`user_id` = `en`.`student_id`)) left join `class_groups` `cg` on(`cg`.`id` = `en`.`class_group_id`)) left join `brazil_cities` `bc` on(`bc`.`id` = `en`.`brazil_city_id`)) where `en`.`status` = 'active' and !exists(select 1 from `enrollment_installments` `ei` where `ei`.`enrollment_id` = `en`.`id` and `ei`.`status` in ('pending','overdue') and `ei`.`due_date` + interval `lr`.`payment_grace_days` day < curdate() limit 1)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `v_teacher_roster`");
    }
};
