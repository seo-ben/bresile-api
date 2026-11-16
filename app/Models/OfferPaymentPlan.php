<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferPaymentPlan extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'offer_payment_plans';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'is_active'    => 'boolean',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(LevelOffer::class, 'offer_id');
    }
}
