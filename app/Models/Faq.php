<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'pregunta',
        'respuesta',
        'palabras_clave',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'bool',
        'orden' => 'int',
    ];

    /** Palabras clave normalizadas como arreglo, para el matcher del bot. */
    public function keywords(): array
    {
        return collect(explode(',', (string) $this->palabras_clave))
            ->map(fn ($k) => trim(mb_strtolower($k)))
            ->filter()
            ->values()
            ->all();
    }
}
