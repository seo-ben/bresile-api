<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormAnswerFile extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'form_answer_files';

    protected $guarded = ['id'];

}