<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAnswer extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    protected $table = 'form_answers';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'value_json' => 'json',
            'updated_at' => 'datetime',
        ];
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }
}