<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsAcceptance extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'terms_acceptances';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
        'accepted_at' => 'datetime',
        ];
    }

}