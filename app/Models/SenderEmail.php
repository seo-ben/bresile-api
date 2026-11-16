<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenderEmail extends Model
{
    use HasFactory;

    protected $table = 'sender_emails';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        ];
    }

}