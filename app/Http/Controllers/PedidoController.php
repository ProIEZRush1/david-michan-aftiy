<?php

namespace App\Http\Controllers;

use App\Models\Numero;
use App\Models\Pedido;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PedidoController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $search = trim((string) $request->input('search', ''));
        $estado = (string) $request->input('estado', '');

        $pedidos = Pedido::query()
            ->with(['plan', 'numero'])
            ->when($search !== '', fn ($q) => $q->where('cliente', 'like', "%{$search}%")
                ->orWhere('telefono', 'like', "%{$search}%"))
            ->when($estado !== '', fn ($q) => $q->where('estado', $estado))
            ->latest('id')
            ->get()
            ->map(fn (Pedido $p) => $this->present($p));

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'filters' => ['search' => $search, 'estado' => $estado],
            'estados' => $this->estadoOptions(),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Pedidos/Create', [
            'planes' => Plan::where('activo', true)->orderBy('orden')->get(['id', 'nombre', 'precio']),
            'estados' => $this->estadoOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cliente' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'plan_id' => ['required', 'exists:planes,id'],
            'estado' => ['required', 'in:'.implode(',', Pedido::ESTADOS)],
            'notas' => ['nullable', 'string'],
        ]);

        $plan = Plan::find($data['plan_id']);
        $data['total'] = $plan?->precio ?? 0;

        Pedido::create($data);

        return redirect()->route('pedidos.index')->with('success', 'Pedido creado correctamente.');
    }

    public function edit(Pedido $pedido): InertiaResponse
    {
        return Inertia::render('Pedidos/Edit', [
            'pedido' => $this->present($pedido),
            'planes' => Plan::where('activo', true)->orderBy('orden')->get(['id', 'nombre', 'precio']),
            'estados' => $this->estadoOptions(),
            // Números asignables: los disponibles + el que ya trae el pedido (si tiene).
            'numeros' => Numero::where('estado', 'disponible')
                ->orWhere('id', $pedido->numero_id)
                ->orderBy('numero')
                ->get(['id', 'numero', 'estado']),
        ]);
    }

    public function update(Request $request, Pedido $pedido): RedirectResponse
    {
        $data = $request->validate([
            'cliente' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'plan_id' => ['required', 'exists:planes,id'],
            'numero_id' => ['nullable', 'exists:numeros,id'],
            'estado' => ['required', 'in:'.implode(',', Pedido::ESTADOS)],
            'notas' => ['nullable', 'string'],
        ]);

        // Reconciliar el inventario si cambió el número asignado.
        $this->syncNumero($pedido, $data['numero_id'] ?? null);

        $plan = Plan::find($data['plan_id']);
        $data['total'] = $plan?->precio ?? $pedido->total;

        $pedido->update($data);

        return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado.');
    }

    public function destroy(Pedido $pedido): RedirectResponse
    {
        // Libera el número de vuelta al inventario.
        if ($pedido->numero_id) {
            Numero::where('id', $pedido->numero_id)->update(['estado' => 'disponible']);
        }
        $pedido->delete();

        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado.');
    }

    /** Marca como asignado el nuevo número y libera el anterior si cambió. */
    private function syncNumero(Pedido $pedido, ?int $nuevoId): void
    {
        $anterior = $pedido->numero_id;
        if ($anterior === $nuevoId) {
            return;
        }

        if ($anterior) {
            Numero::where('id', $anterior)->update(['estado' => 'disponible']);
        }
        if ($nuevoId) {
            Numero::where('id', $nuevoId)->update(['estado' => 'asignado']);
        }
    }

    private function present(Pedido $pedido): array
    {
        return [
            'id' => $pedido->id,
            'cliente' => $pedido->cliente,
            'telefono' => $pedido->telefono,
            'email' => $pedido->email,
            'plan_id' => $pedido->plan_id,
            'plan' => $pedido->plan?->nombre,
            'numero_id' => $pedido->numero_id,
            'numero' => $pedido->numero?->numero,
            'estado' => $pedido->estado,
            'estado_label' => Pedido::estadoLabel($pedido->estado),
            'total' => $pedido->total,
            'link_pago' => $pedido->link_pago,
            'notas' => $pedido->notas,
            'creado' => $pedido->created_at?->format('d/m/Y H:i'),
        ];
    }

    /** @return array<int,array{value:string,label:string}> */
    private function estadoOptions(): array
    {
        return collect(Pedido::ESTADOS)
            ->map(fn ($e) => ['value' => $e, 'label' => Pedido::estadoLabel($e)])
            ->all();
    }
}
