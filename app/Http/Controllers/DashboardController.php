<?php

namespace App\Http\Controllers;

use App\Models\BotContact;
use App\Models\Cliente;
use App\Models\Faq;
use App\Models\Numero;
use App\Models\Pedido;
use App\Models\Plan;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    /** Panel principal con conteos reales por módulo del negocio. */
    public function index(): InertiaResponse
    {
        $ingresos = (int) Pedido::whereIn('estado', ['pagado', 'numero_asignado', 'entregado'])->sum('total');

        $pedidosPorEstado = Pedido::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return Inertia::render('Dashboard', [
            'metrics' => [
                'planes' => Plan::where('activo', true)->count(),
                'numeros_disponibles' => Numero::where('estado', 'disponible')->count(),
                'numeros_total' => Numero::count(),
                'pedidos' => Pedido::count(),
                'pedidos_abiertos' => Pedido::whereNotIn('estado', ['entregado', 'cancelado'])->count(),
                'clientes' => Cliente::count(),
                'faqs' => Faq::where('activo', true)->count(),
                'conversaciones' => BotContact::count(),
                'conversaciones_humano' => BotContact::where('step', 'human')->count(),
                'ingresos' => $ingresos,
            ],
            'pedidosPorEstado' => $pedidosPorEstado,
            'ultimosPedidos' => Pedido::with(['plan', 'numero'])
                ->latest('id')
                ->take(5)
                ->get()
                ->map(fn (Pedido $p) => [
                    'id' => $p->id,
                    'cliente' => $p->cliente,
                    'telefono' => $p->telefono,
                    'plan' => $p->plan?->nombre,
                    'estado' => $p->estado,
                    'estado_label' => Pedido::estadoLabel($p->estado),
                    'total' => $p->total,
                    'numero' => $p->numero?->numero,
                ]),
        ]);
    }
}
