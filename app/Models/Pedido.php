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
        'numero_id',
        'cliente',
        'telefono',
        'email',
        'estado',
        'total',
        'link_pago',
        'notas',
    ];

    protected $casts = [
        'total' => 'int',
    ];

    /** Flujo de estados del pedido, en orden. */
    public const ESTADOS = [
        'iniciado',
        'en_pago',
        'pagado',
        'numero_asignado',
        'entregado',
        'cancelado',
    ];

    /** Etiqueta legible en español para un estado. */
    public static function estadoLabel(string $estado): string
    {
        return [
            'iniciado' => 'Iniciado',
            'en_pago' => 'En pago',
            'pagado' => 'Pagado',
            'numero_asignado' => 'Número asignado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ][$estado] ?? ucfirst($estado);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function numero(): BelongsTo
    {
        return $this->belongsTo(Numero::class);
    }

    public function botContact(): BelongsTo
    {
        return $this->belongsTo(BotContact::class);
    }
}
