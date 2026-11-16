<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundPolicy extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'refund_policies';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'refund_percent' => 'decimal:2',
        'is_active' => 'boolean',
        ];
    }

}