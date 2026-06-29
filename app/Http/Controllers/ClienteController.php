<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ClienteController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $search = trim((string) $request->input('search', ''));

        $clientes = Cliente::query()
            ->when($search !== '', fn ($q) => $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('telefono', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->orderBy('nombre')
            ->get()
            ->map(fn (Cliente $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'telefono' => $c->telefono,
                'email' => $c->email,
                'notas' => $c->notas,
                'pedidos_count' => $c->pedidos()->count(),
            ]);

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Clientes/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Cliente::create($this->validateData($request));

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado.');
    }

    public function edit(Cliente $cliente): InertiaResponse
    {
        return Inertia::render('Clientes/Edit', ['cliente' => $cliente]);
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($this->validateData($request, $cliente->id));

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:30', 'unique:clientes,telefono'.($id ? ",{$id}" : '')],
            'email' => ['nullable', 'email', 'max:255'],
            'notas' => ['nullable', 'string'],
        ]);
    }
}
