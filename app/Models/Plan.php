<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'precio',
        'descripcion',
        'datos',
        'minutos',
        'sms',
        'vigencia_dias',
        'activo',
        'orden',
    ];

    protected $casts = [
        'precio' => 'int',
        'vigencia_dias' => 'int',
        'activo' => 'bool',
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
