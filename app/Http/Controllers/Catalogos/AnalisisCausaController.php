<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\AnalisisCausa;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalisisCausaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/AnalisisCausas', [
            'items' => AnalisisCausa::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200', 'unique:analisis_causas,nombre'],
            'activo' => ['required', 'boolean'],
        ]);

        AnalisisCausa::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Análisis de causa creado.')]);

        return back();
    }

    public function update(Request $request, AnalisisCausa $analisisCausa): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200', 'unique:analisis_causas,nombre,'.$analisisCausa->id],
            'activo' => ['required', 'boolean'],
        ]);

        $analisisCausa->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Análisis de causa actualizado.')]);

        return back();
    }

    public function destroy(AnalisisCausa $analisisCausa): RedirectResponse
    {
        try {
            $analisisCausa->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso. Desactívalo en su lugar.', ['nombre' => $analisisCausa->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Análisis de causa eliminado.')]);

        return back();
    }
}
