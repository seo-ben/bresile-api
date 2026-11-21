<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_offers', function (Blueprint $table) {
            if (! Schema::hasColumn('level_offers', 'evening_enabled')) {
                $table->boolean('evening_enabled')->default(false)->after('study_mode');
            }
            if (! Schema::hasColumn('level_offers', 'day_schedule_label')) {
                $table->string('day_schedule_label', 120)->nullable()->after('evening_enabled');
            }
            if (! Schema::hasColumn('level_offers', 'evening_schedule_label')) {
                $table->string('evening_schedule_label', 120)->nullable()->after('day_schedule_label');
            }
            if (! Schema::hasColumn('level_offers', 'evening_base_price')) {
                $table->decimal('evening_base_price', 10, 2)->nullable()->after('base_price');
            }
            if (! Schema::hasColumn('level_offers', 'evening_installment_1_amount')) {
                $table->decimal('evening_installment_1_amount', 10, 2)->nullable()->after('installment_3_amount');
            }
            if (! Schema::hasColumn('level_offers', 'evening_installment_2_amount')) {
                $table->decimal('evening_installment_2_amount', 10, 2)->nullable()->after('evening_installment_1_amount');
            }
            if (! Schema::hasColumn('level_offers', 'evening_installment_3_amount')) {
                $table->decimal('evening_installment_3_amount', 10, 2)->nullable()->after('evening_installment_2_amount');
            }
        });

        // Initialiser les créneaux du soir avec tarification modulée sur les offres En Ligne existantes
        $onlineOffers = DB::table('level_offers')
            ->where('study_mode', 'remote_home_country')
            ->get();

        foreach ($onlineOffers as $offer) {
            $base = (float) ($offer->base_price ?? 350);
            $eveningBase = round($base + 60, 2);
            $t2 = floor(($eveningBase / 3) / 5) * 5;
            if ($t2 <= 0) {
                $t2 = round($eveningBase / 3, 2);
            }
            $t3 = $t2;
            $t1 = round($eveningBase - $t2 - $t3, 2);

            DB::table('level_offers')
                ->where('id', $offer->id)
                ->update([
                    'evening_enabled'              => true,
                    'day_schedule_label'           => $offer->day_schedule_label ?: '09h00 – 13h00',
                    'evening_schedule_label'       => $offer->evening_schedule_label ?: '18h30 – 21h30',
                    'evening_base_price'           => $offer->evening_base_price ?? $eveningBase,
                    'evening_installment_1_amount' => $offer->evening_installment_1_amount ?? $t1,
                    'evening_installment_2_amount' => $offer->evening_installment_2_amount ?? $t2,
                    'evening_installment_3_amount' => $offer->evening_installment_3_amount ?? $t3,
                ]);
        }

        DB::table('level_offers')
            ->where('study_mode', '!=', 'remote_home_country')
            ->whereNull('day_schedule_label')
            ->update([
                'day_schedule_label'     => '09h00 – 13h00',
                'evening_schedule_label' => '18h30 – 21h30',
            ]);
    }

    public function down(): void
    {
        Schema::table('level_offers', function (Blueprint $table) {
            $cols = [
                'evening_enabled',
                'day_schedule_label',
                'evening_schedule_label',
                'evening_base_price',
                'evening_installment_1_amount',
                'evening_installment_2_amount',
                'evening_installment_3_amount',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('level_offers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
