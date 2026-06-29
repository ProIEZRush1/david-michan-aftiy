<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PlanController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $search = trim((string) $request->input('search', ''));

        $planes = Plan::query()
            ->when($search !== '', fn ($q) => $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('descripcion', 'like', "%{$search}%"))
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return Inertia::render('Planes/Index', [
            'planes' => $planes,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Planes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Plan::create($this->validateData($request));

        return redirect()->route('planes.index')->with('success', 'Plan creado correctamente.');
    }

    public function edit(Plan $plan): InertiaResponse
    {
        return Inertia::render('Planes/Edit', [
            'plan' => array_merge($plan->toArray(), [
                'precio' => round($plan->precio / 100, 2), // a pesos para el formulario
            ]),
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validateData($request));

        return redirect()->route('planes.index')->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('planes.index')->with('success', 'Plan eliminado.');
    }

    /** Valida y normaliza el precio (ingresado en pesos) a centavos. */
    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string'],
            'datos' => ['nullable', 'string', 'max:255'],
            'minutos' => ['nullable', 'string', 'max:255'],
            'sms' => ['nullable', 'string', 'max:255'],
            'vigencia_dias' => ['required', 'integer', 'min:1'],
            'activo' => ['boolean'],
            'orden' => ['nullable', 'integer'],
        ]);

        $data['precio'] = (int) round(((float) $data['precio']) * 100);
        $data['orden'] = $data['orden'] ?? 0;
        $data['activo'] = $request->boolean('activo');

        return $data;
    }
}
