<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\ClasificacionIncidente;
use App\Models\TipoIncidente;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClasificacionIncidenteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/Clasificaciones', [
            'items' => ClasificacionIncidente::with('tipoIncidente:id,nombre')
                ->orderBy('tipo_incidente_id')
                ->orderBy('nombre')
                ->get(['id', 'tipo_incidente_id', 'nombre', 'activo']),
            'tiposIncidente' => TipoIncidente::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_incidente_id' => ['required', 'integer', 'exists:tipos_incidente,id'],
            'nombre' => [
                'required',
                'string',
                'max:200',
                Rule::unique('clasificaciones_incidente', 'nombre')
                    ->where('tipo_incidente_id', $request->input('tipo_incidente_id')),
            ],
            'activo' => ['required', 'boolean'],
        ]);

        ClasificacionIncidente::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Clasificación creada.')]);

        return back();
    }

    public function update(Request $request, ClasificacionIncidente $clasificacion): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_incidente_id' => ['required', 'integer', 'exists:tipos_incidente,id'],
            'nombre' => [
                'required',
                'string',
                'max:200',
                Rule::unique('clasificaciones_incidente', 'nombre')
                    ->where('tipo_incidente_id', $request->input('tipo_incidente_id'))
                    ->ignore($clasificacion->id),
            ],
            'activo' => ['required', 'boolean'],
        ]);

        $clasificacion->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Clasificación actualizada.')]);

        return back();
    }

    public function destroy(ClasificacionIncidente $clasificacion): RedirectResponse
    {
        try {
            $clasificacion->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso. Desactívala en su lugar.', ['nombre' => $clasificacion->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Clasificación eliminada.')]);

        return back();
    }
}
