<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileModel extends Model
{
    public const UPDATED_AT = null;
    use HasFactory;

    protected $table = 'files';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'created_at' => 'datetime',
        ];
    }

}