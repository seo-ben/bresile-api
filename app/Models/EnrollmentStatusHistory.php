<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentStatusHistory extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'enrollment_status_history';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'changed_at' => 'datetime',
        ];
    }

}