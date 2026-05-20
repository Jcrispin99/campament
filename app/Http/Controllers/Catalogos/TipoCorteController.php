<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\TipoCorte;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoCorteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/TiposCorte', [
            'items' => TipoCorte::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:tipos_corte,nombre'],
            'activo' => ['required', 'boolean'],
        ]);

        TipoCorte::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de corte creado.')]);

        return back();
    }

    public function update(Request $request, TipoCorte $tipoCorte): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:tipos_corte,nombre,'.$tipoCorte->id],
            'activo' => ['required', 'boolean'],
        ]);

        $tipoCorte->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de corte actualizado.')]);

        return back();
    }

    public function destroy(TipoCorte $tipoCorte): RedirectResponse
    {
        try {
            $tipoCorte->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso por uno o más gramajes.', ['nombre' => $tipoCorte->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de corte eliminado.')]);

        return back();
    }
}
