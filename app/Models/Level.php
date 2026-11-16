<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Level extends Model
{
    use HasFactory;

    protected $table = 'levels';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rules(): HasOne
    {
        return $this->hasOne(LevelRule::class, 'level_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(LevelOffer::class, 'level_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'level_id');
    }
}
