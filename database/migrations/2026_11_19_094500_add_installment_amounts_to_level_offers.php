<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('level_offers')) {
            Schema::table('level_offers', function (Blueprint $table) {
                if (! Schema::hasColumn('level_offers', 'installment_1_amount')) {
                    $table->decimal('installment_1_amount', 12, 2)->nullable()->after('base_price');
                }
                if (! Schema::hasColumn('level_offers', 'installment_2_amount')) {
                    $table->decimal('installment_2_amount', 12, 2)->nullable()->after('installment_1_amount');
                }
                if (! Schema::hasColumn('level_offers', 'installment_3_amount')) {
                    $table->decimal('installment_3_amount', 12, 2)->nullable()->after('installment_2_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('level_offers')) {
            Schema::table('level_offers', function (Blueprint $table) {
                $cols = [];
                foreach (['installment_1_amount', 'installment_2_amount', 'installment_3_amount'] as $col) {
                    if (Schema::hasColumn('level_offers', $col)) {
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
