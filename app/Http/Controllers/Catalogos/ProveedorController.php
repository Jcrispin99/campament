<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/Proveedores', [
            'items' => Proveedor::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:proveedores,nombre'],
            'activo' => ['required', 'boolean'],
        ]);

        Proveedor::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Proveedor creado.')]);

        return back();
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:proveedores,nombre,'.$proveedor->id],
            'activo' => ['required', 'boolean'],
        ]);

        $proveedor->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Proveedor actualizado.')]);

        return back();
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        try {
            $proveedor->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso. Desactívalo en su lugar.', ['nombre' => $proveedor->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Proveedor eliminado.')]);

        return back();
    }
}
