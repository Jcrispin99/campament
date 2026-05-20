<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Plato;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/Platos', [
            'items' => Plato::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:platos,nombre'],
            'activo' => ['required', 'boolean'],
        ]);

        Plato::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plato creado.')]);

        return back();
    }

    public function update(Request $request, Plato $plato): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:platos,nombre,'.$plato->id],
            'activo' => ['required', 'boolean'],
        ]);

        $plato->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plato actualizado.')]);

        return back();
    }

    public function destroy(Plato $plato): RedirectResponse
    {
        try {
            $plato->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso. Desactívalo en su lugar.', ['nombre' => $plato->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plato eliminado.')]);

        return back();
    }
}
