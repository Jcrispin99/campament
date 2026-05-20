<?php

namespace App\Http\Controllers\Catalogos;

use App\Enums\UnidadGramaje;
use App\Http\Controllers\Controller;
use App\Models\Componente;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ComponenteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/Componentes', [
            'items' => Componente::orderBy('nombre')->get([
                'id',
                'nombre',
                'gramaje_sugerido',
                'unidad',
                'observacion',
                'activo',
            ]),
            'unidades' => UnidadGramaje::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200', 'unique:componentes,nombre'],
            'gramaje_sugerido' => ['required', 'numeric', 'gt:0'],
            'unidad' => ['required', Rule::enum(UnidadGramaje::class)],
            'observacion' => ['nullable', 'string', 'max:200'],
            'activo' => ['required', 'boolean'],
        ]);

        Componente::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente creado.')]);

        return back();
    }

    public function update(Request $request, Componente $componente): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200', 'unique:componentes,nombre,'.$componente->id],
            'gramaje_sugerido' => ['required', 'numeric', 'gt:0'],
            'unidad' => ['required', Rule::enum(UnidadGramaje::class)],
            'observacion' => ['nullable', 'string', 'max:200'],
            'activo' => ['required', 'boolean'],
        ]);

        $componente->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente actualizado.')]);

        return back();
    }

    public function destroy(Componente $componente): RedirectResponse
    {
        try {
            $componente->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso por uno o más gramajes. Desactívalo en su lugar.', ['nombre' => $componente->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente eliminado.')]);

        return back();
    }
}
