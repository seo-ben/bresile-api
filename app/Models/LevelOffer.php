<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LevelOffer extends Model
{
    use HasFactory;

    protected $table = 'level_offers';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_available'                 => 'boolean',
            'allow_requests'               => 'boolean',
            'evening_enabled'              => 'boolean',
            'base_price'                   => 'decimal:2',
            'evening_base_price'           => 'decimal:2',
            'evening_installment_1_amount' => 'decimal:2',
            'evening_installment_2_amount' => 'decimal:2',
            'evening_installment_3_amount' => 'decimal:2',
            'is_active'                    => 'boolean',
            'created_at'                   => 'datetime',
            'updated_at'                   => 'datetime',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function prerequisiteLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'prerequisite_level_id');
    }

    public function paymentPlans(): HasMany
    {
        return $this->hasMany(OfferPaymentPlan::class, 'offer_id');
    }
}
