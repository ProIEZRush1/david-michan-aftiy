<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'notas',
    ];

    /** Pedidos del cliente, ligados por número de teléfono. */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'telefono', 'telefono');
    }
}
