<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Comedor;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComedorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/Comedores', [
            'items' => Comedor::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:comedores,nombre'],
            'activo' => ['required', 'boolean'],
        ]);

        Comedor::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Comedor creado.')]);

        return back();
    }

    public function update(Request $request, Comedor $comedor): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:comedores,nombre,'.$comedor->id],
            'activo' => ['required', 'boolean'],
        ]);

        $comedor->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Comedor actualizado.')]);

        return back();
    }

    public function destroy(Comedor $comedor): RedirectResponse
    {
        try {
            $comedor->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso. Desactívalo en su lugar.', ['nombre' => $comedor->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Comedor eliminado.')]);

        return back();
    }
}
