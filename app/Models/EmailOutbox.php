<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailOutbox extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $table = 'email_outbox';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'sent_at'   => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
