<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrazilCity extends Model
{
    use HasFactory;

    protected $table = 'brazil_cities';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'is_available' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        ];
    }

}