<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServicioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/Servicios', [
            'items' => Servicio::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:servicios,nombre'],
            'activo' => ['required', 'boolean'],
        ]);

        Servicio::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Servicio creado.')]);

        return back();
    }

    public function update(Request $request, Servicio $servicio): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:servicios,nombre,'.$servicio->id],
            'activo' => ['required', 'boolean'],
        ]);

        $servicio->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Servicio actualizado.')]);

        return back();
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        try {
            $servicio->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso. Desactívalo en su lugar.', ['nombre' => $servicio->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Servicio eliminado.')]);

        return back();
    }
}
