<?php

namespace App\Http\Controllers;

use App\Models\BotContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ConversacionController extends Controller
{
    /** Etiquetas legibles de los pasos del bot. */
    private const STEP_LABELS = [
        'new' => 'Nuevo',
        'choosing' => 'Eligiendo plan',
        'confirming' => 'Confirmando',
        'paying' => 'En pago',
        'done' => 'Cerrado',
        'human' => 'Con asesor',
    ];

    public function index(Request $request): InertiaResponse
    {
        $search = trim((string) $request->input('search', ''));

        $conversaciones = BotContact::query()
            ->withCount('pedidos')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"))
            ->orderByRaw("CASE WHEN step = 'human' THEN 0 ELSE 1 END")
            ->latest('updated_at')
            ->get()
            ->map(fn (BotContact $c) => [
                'id' => $c->id,
                'phone' => $c->phone,
                'name' => $c->name,
                'step' => $c->step,
                'step_label' => self::STEP_LABELS[$c->step] ?? ucfirst($c->step),
                'pedidos_count' => $c->pedidos_count,
                'actualizado' => $c->updated_at?->diffForHumans(),
            ]);

        return Inertia::render('Conversaciones/Index', [
            'conversaciones' => $conversaciones,
            'filters' => ['search' => $search],
            'resumen' => [
                'total' => BotContact::count(),
                'con_asesor' => BotContact::where('step', 'human')->count(),
                'en_proceso' => BotContact::whereIn('step', ['choosing', 'confirming', 'paying'])->count(),
            ],
        ]);
    }

    /** Tomar la conversación: pasa el chat a un asesor humano (el bot calla). */
    public function tomar(BotContact $conversacion): RedirectResponse
    {
        $conversacion->update(['step' => 'human']);

        return back()->with('success', 'Conversación transferida a un asesor.');
    }

    /** Devolver al bot: el flujo automático retoma desde el inicio. */
    public function devolver(BotContact $conversacion): RedirectResponse
    {
        $conversacion->update(['step' => 'new']);

        return back()->with('success', 'Conversación devuelta al bot.');
    }
}
