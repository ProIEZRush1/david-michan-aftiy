<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotContact extends Model
{
    protected $fillable = [
        'phone',
        'name',
        'step',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
