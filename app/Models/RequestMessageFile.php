<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestMessageFile extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'request_message_files';

    protected $guarded = ['id'];

}