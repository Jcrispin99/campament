<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\TipoIncidente;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoIncidenteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/TiposIncidente', [
            'items' => TipoIncidente::withCount('clasificaciones')
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:tipos_incidente,nombre'],
        ]);

        TipoIncidente::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de incidente creado.')]);

        return back();
    }

    public function update(Request $request, TipoIncidente $tipoIncidente): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:tipos_incidente,nombre,'.$tipoIncidente->id],
        ]);

        $tipoIncidente->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de incidente actualizado.')]);

        return back();
    }

    public function destroy(TipoIncidente $tipoIncidente): RedirectResponse
    {
        try {
            $tipoIncidente->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso por reportes existentes.', ['nombre' => $tipoIncidente->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de incidente eliminado.')]);

        return back();
    }
}
