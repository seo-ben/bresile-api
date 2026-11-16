<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestMessage extends Model
{
    public const UPDATED_AT = null;
    use HasFactory;

    protected $table = 'request_messages';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        ];
    }

}