<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\TipoProducto;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TipoProductoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Catalogos/TiposProducto', [
            'items' => TipoProducto::orderBy('nombre')->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:tipos_producto,nombre'],
            'activo' => ['required', 'boolean'],
        ]);

        TipoProducto::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de producto creado.')]);

        return back();
    }

    public function update(Request $request, TipoProducto $tipoProducto): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:tipos_producto,nombre,'.$tipoProducto->id],
            'activo' => ['required', 'boolean'],
        ]);

        $tipoProducto->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de producto actualizado.')]);

        return back();
    }

    public function destroy(TipoProducto $tipoProducto): RedirectResponse
    {
        try {
            $tipoProducto->delete();
        } catch (QueryException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar «:nombre»: está en uso. Desactívalo en su lugar.', ['nombre' => $tipoProducto->nombre]),
            ]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tipo de producto eliminado.')]);

        return back();
    }
}
