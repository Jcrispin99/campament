<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Origen;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrigenController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/Origenes', [
            'items' => Origen::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:origenes,nombre'],
            'activo' => ['required', 'boolean'],
        ]);

        Origen::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Origen creado.')]);

        return back();
    }

    public function update(Request $request, Origen $origen): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:origenes,nombre,'.$origen->id],
            'activo' => ['required', 'boolean'],
        ]);

        $origen->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Origen actualizado.')]);

        return back();
    }

    public function destroy(Origen $origen): RedirectResponse
    {
        try {
            $origen->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso. Desactívalo en su lugar.', ['nombre' => $origen->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Origen eliminado.')]);

        return back();
    }
}
