<?php

namespace App\Http\Controllers;

use App\Enums\EstatusGramaje;
use App\Enums\UnidadGramaje;
use App\Exports\GramajesExport;
use App\Http\Requests\Gramajes\StoreGramajeRequest;
use App\Http\Requests\Gramajes\UpdateGramajeRequest;
use App\Models\Comedor;
use App\Models\Componente;
use App\Models\Gramaje;
use App\Models\Plato;
use App\Models\Servicio;
use App\Models\TipoCorte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GramajeController extends Controller
{
    public function index(Request $request): Response
    {
        $gramajes = Gramaje::query()
            ->with([
                'comedor:id,nombre',
                'servicio:id,nombre',
                'plato:id,nombre',
                'componente:id,nombre,unidad',
                'tipoCorte:id,nombre',
                'reportadoPor:id,name',
            ])
            ->latest('fecha')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Gramajes/Index', [
            'gramajes' => $gramajes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Gramajes/Create', [
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function store(StoreGramajeRequest $request): RedirectResponse
    {
        $gramaje = DB::transaction(function () use ($request) {
            $base = $request->safe()->except(['medidas']);
            /** @var array<int, numeric-string> $medidas */
            $medidas = $request->input('medidas', []);

            $derivados = $this->calcularDerivados($medidas, (float) $base['gramaje_esperado']);

            $gramaje = Gramaje::create([
                ...$base,
                ...$derivados,
                'reportado_por_id' => $request->user()->id,
            ]);

            foreach (array_values($medidas) as $idx => $peso) {
                $gramaje->medidas()->create([
                    'peso' => $peso,
                    'orden' => $idx,
                ]);
            }

            return $gramaje;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Gramaje creado.')]);

        if ($request->boolean('crear_otro')) {
            return to_route('gramajes.create');
        }

        return to_route('gramajes.show', $gramaje);
    }

    public function show(Gramaje $gramaje): Response
    {
        $gramaje->load([
            'comedor',
            'servicio',
            'plato',
            'componente',
            'tipoCorte',
            'reportadoPor:id,name,email',
            'medidas',
        ]);

        return Inertia::render('Gramajes/Show', [
            'gramaje' => $gramaje,
        ]);
    }

    public function edit(Gramaje $gramaje): Response
    {
        $gramaje->load(['medidas']);

        return Inertia::render('Gramajes/Edit', [
            'gramaje' => $gramaje,
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function update(UpdateGramajeRequest $request, Gramaje $gramaje): RedirectResponse
    {
        DB::transaction(function () use ($request, $gramaje) {
            $base = $request->safe()->except(['medidas']);
            /** @var array<int, numeric-string> $medidas */
            $medidas = $request->input('medidas', []);

            $derivados = $this->calcularDerivados($medidas, (float) $base['gramaje_esperado']);

            $gramaje->update([
                ...$base,
                ...$derivados,
            ]);

            $gramaje->medidas()->delete();
            foreach (array_values($medidas) as $idx => $peso) {
                $gramaje->medidas()->create([
                    'peso' => $peso,
                    'orden' => $idx,
                ]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Gramaje actualizado.')]);

        return to_route('gramajes.show', $gramaje);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $filename = 'gramajes-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(
            new GramajesExport($validated['desde'] ?? null, $validated['hasta'] ?? null),
            $filename,
        );
    }

    public function destroy(Gramaje $gramaje): RedirectResponse
    {
        $gramaje->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Gramaje eliminado.')]);

        return to_route('gramajes.index');
    }

    /**
     * @param  array<int, numeric-string|float|int>  $medidas
     * @return array{cantidad_muestreada: int, peso_promedio: float, variacion_pct: float, estatus: string}
     */
    private function calcularDerivados(array $medidas, float $gramajeEsperado): array
    {
        $cantidad = count($medidas);
        $promedio = $cantidad > 0
            ? round(array_sum(array_map('floatval', $medidas)) / $cantidad, 2)
            : 0.0;
        $variacion = $gramajeEsperado > 0
            ? round(($promedio / $gramajeEsperado) * 100, 2)
            : 0.0;

        return [
            'cantidad_muestreada' => $cantidad,
            'peso_promedio' => $promedio,
            'variacion_pct' => $variacion,
            'estatus' => EstatusGramaje::fromVariacion($variacion)->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogos(): array
    {
        return [
            'comedores' => Comedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'servicios' => Servicio::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'platos' => Plato::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'componentes' => Componente::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'gramaje_sugerido', 'unidad', 'observacion']),
            'tiposCorte' => TipoCorte::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'unidades' => UnidadGramaje::options(),
        ];
    }
}
