<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'bot_contact_id',
        'plan_id',
        'cliente',
        'telefono',
        'estado',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function botContact(): BelongsTo
    {
        return $this->belongsTo(BotContact::class);
    }
}
