<?php

namespace App\Http\Controllers;

use App\Enums\ConformidadMp;
use App\Exports\MateriasPrimasExport;
use App\Http\Requests\MateriasPrimas\StoreMateriaPrimaRequest;
use App\Http\Requests\MateriasPrimas\UpdateMateriaPrimaRequest;
use App\Models\MateriaPrima;
use App\Models\Origen;
use App\Models\Proveedor;
use App\Models\TipoProducto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MateriaPrimaController extends Controller
{
    public function index(Request $request): Response
    {
        $materiasPrimas = MateriaPrima::query()
            ->with([
                'tipoProducto:id,nombre',
                'proveedor:id,nombre',
                'origen:id,nombre',
                'reportadoPor:id,name',
            ])
            ->latest('fecha')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('MateriasPrimas/Index', [
            'materiasPrimas' => $materiasPrimas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MateriasPrimas/Create', [
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function store(StoreMateriaPrimaRequest $request): RedirectResponse
    {
        $materiaPrima = MateriaPrima::create([
            ...$request->safe()->all(),
            'reportado_por_id' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Recepción de materia prima registrada.')]);

        if ($request->boolean('crear_otro')) {
            return to_route('materias-primas.create');
        }

        return to_route('materias-primas.show', $materiaPrima);
    }

    public function show(MateriaPrima $materiaPrima): Response
    {
        $materiaPrima->load([
            'tipoProducto',
            'proveedor',
            'origen',
            'reportadoPor:id,name,email',
        ]);

        return Inertia::render('MateriasPrimas/Show', [
            'materiaPrima' => $materiaPrima,
        ]);
    }

    public function edit(MateriaPrima $materiaPrima): Response
    {
        return Inertia::render('MateriasPrimas/Edit', [
            'materiaPrima' => $materiaPrima,
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function update(UpdateMateriaPrimaRequest $request, MateriaPrima $materiaPrima): RedirectResponse
    {
        $materiaPrima->update($request->safe()->all());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Materia prima actualizada.')]);

        return to_route('materias-primas.show', $materiaPrima);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $filename = 'materias-primas-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(
            new MateriasPrimasExport($validated['desde'] ?? null, $validated['hasta'] ?? null),
            $filename,
        );
    }

    public function destroy(MateriaPrima $materiaPrima): RedirectResponse
    {
        $materiaPrima->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Registro eliminado.')]);

        return to_route('materias-primas.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogos(): array
    {
        return [
            'tiposProducto' => TipoProducto::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'proveedores' => Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'origenes' => Origen::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'conformidades' => ConformidadMp::options(),
        ];
    }
}
