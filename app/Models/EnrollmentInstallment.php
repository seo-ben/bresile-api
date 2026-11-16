<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentInstallment extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'enrollment_installments';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        ];
    }

}