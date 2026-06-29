<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FaqController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $search = trim((string) $request->input('search', ''));

        $faqs = Faq::query()
            ->when($search !== '', fn ($q) => $q->where('pregunta', 'like', "%{$search}%")
                ->orWhere('respuesta', 'like', "%{$search}%")
                ->orWhere('palabras_clave', 'like', "%{$search}%"))
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return Inertia::render('Faqs/Index', [
            'faqs' => $faqs,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Faqs/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validateData($request));

        return redirect()->route('faqs.index')->with('success', 'Pregunta frecuente creada.');
    }

    public function edit(Faq $faq): InertiaResponse
    {
        return Inertia::render('Faqs/Edit', ['faq' => $faq]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validateData($request));

        return redirect()->route('faqs.index')->with('success', 'Pregunta frecuente actualizada.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('faqs.index')->with('success', 'Pregunta frecuente eliminada.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'pregunta' => ['required', 'string', 'max:255'],
            'respuesta' => ['required', 'string'],
            'palabras_clave' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
            'orden' => ['nullable', 'integer'],
        ]);

        $data['orden'] = $data['orden'] ?? 0;
        $data['activo'] = $request->boolean('activo');

        return $data;
    }
}
