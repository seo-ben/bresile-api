<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WhatsappLead extends Model
{
    protected $table = 'whatsapp_leads';

    protected $fillable = [
        'dial_code',
        'country_name',
        'phone_number',
        'full_whatsapp',
        'locale',
        'status',
        'admin_note',
    ];

    /**
     * S'assure que la table existe en base (auto-migration transparente sur Wasmer).
     */
    public static function ensureTableExists(): void
    {
        if (! Schema::hasTable('whatsapp_leads')) {
            Schema::create('whatsapp_leads', function (Blueprint $table) {
                $table->id();
                $table->string('dial_code', 12)->default('+221');
                $table->string('country_name', 80)->nullable();
                $table->string('phone_number', 40);
                $table->string('full_whatsapp', 60);
                $table->string('locale', 10)->default('fr');
                $table->string('status', 30)->default('new');
                $table->text('admin_note')->nullable();
                $table->timestamps();
            });
        }
    }
}
