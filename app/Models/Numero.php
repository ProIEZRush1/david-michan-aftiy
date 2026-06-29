<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Numero extends Model
{
    protected $table = 'numeros';

    protected $fillable = [
        'numero',
        'lada',
        'tipo',
        'estado',
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /** Etiqueta legible en español para un estado de inventario. */
    public static function estadoLabel(string $estado): string
    {
        return [
            'disponible' => 'Disponible',
            'reservado' => 'Reservado',
            'asignado' => 'Asignado',
        ][$estado] ?? ucfirst($estado);
    }
}
