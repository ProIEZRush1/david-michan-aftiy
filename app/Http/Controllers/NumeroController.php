<?php

namespace App\Http\Controllers;

use App\Models\Numero;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NumeroController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $search = trim((string) $request->input('search', ''));
        $estado = (string) $request->input('estado', '');

        $numeros = Numero::query()
            ->when($search !== '', fn ($q) => $q->where('numero', 'like', "%{$search}%")
                ->orWhere('lada', 'like', "%{$search}%"))
            ->when($estado !== '', fn ($q) => $q->where('estado', $estado))
            ->orderByRaw("CASE estado WHEN 'disponible' THEN 0 WHEN 'reservado' THEN 1 ELSE 2 END")
            ->orderBy('numero')
            ->get();

        return Inertia::render('Numeros/Index', [
            'numeros' => $numeros,
            'filters' => ['search' => $search, 'estado' => $estado],
            'resumen' => [
                'disponible' => Numero::where('estado', 'disponible')->count(),
                'reservado' => Numero::where('estado', 'reservado')->count(),
                'asignado' => Numero::where('estado', 'asignado')->count(),
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Numeros/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Numero::create($this->validateData($request));

        return redirect()->route('numeros.index')->with('success', 'Número agregado al inventario.');
    }

    public function edit(Numero $numero): InertiaResponse
    {
        return Inertia::render('Numeros/Edit', ['numero' => $numero]);
    }

    public function update(Request $request, Numero $numero): RedirectResponse
    {
        $numero->update($this->validateData($request, $numero->id));

        return redirect()->route('numeros.index')->with('success', 'Número actualizado.');
    }

    public function destroy(Numero $numero): RedirectResponse
    {
        $numero->delete();

        return redirect()->route('numeros.index')->with('success', 'Número eliminado del inventario.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'numero' => ['required', 'string', 'max:30', 'unique:numeros,numero'.($id ? ",{$id}" : '')],
            'lada' => ['nullable', 'string', 'max:10'],
            'tipo' => ['required', 'in:nuevo,portabilidad'],
            'estado' => ['required', 'in:disponible,reservado,asignado'],
        ]);
    }
}
