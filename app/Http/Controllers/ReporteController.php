<?php

namespace App\Http\Controllers;

use App\Enums\Criticidad;
use App\Exports\ReportesExport;
use App\Http\Requests\Reportes\StoreReporteRequest;
use App\Http\Requests\Reportes\UpdateReporteRequest;
use App\Models\AnalisisCausa;
use App\Models\Comedor;
use App\Models\Evidencia;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\TipoIncidente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteController extends Controller
{
    public function index(Request $request): Response
    {
        $reportes = Reporte::query()
            ->with([
                'comedor:id,nombre',
                'servicio:id,nombre',
                'tipoIncidente:id,nombre',
                'clasificacion:id,nombre,tipo_incidente_id',
                'analisisCausa:id,nombre',
                'reportadoPor:id,name',
            ])
            ->latest('fecha')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Reportes/Index', [
            'reportes' => $reportes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Reportes/Create', [
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function store(StoreReporteRequest $request): RedirectResponse
    {
        $reporte = DB::transaction(function () use ($request) {
            $reporte = Reporte::create([
                ...$request->safe()->except(['evidencias']),
                'reportado_por_id' => $request->user()->id,
            ]);

            foreach ($request->file('evidencias', []) as $idx => $evidencia) {
                $path = $evidencia['imagen']->store('evidencias', 'public');
                $reporte->evidencias()->create([
                    'imagen' => $path,
                    'descripcion' => $request->input("evidencias.$idx.descripcion"),
                ]);
            }

            return $reporte;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reporte creado.')]);

        return to_route('reportes.show', $reporte);
    }

    public function show(Reporte $reporte): Response
    {
        $reporte->load([
            'comedor',
            'servicio',
            'tipoIncidente',
            'clasificacion.tipoIncidente',
            'analisisCausa',
            'reportadoPor:id,name,email',
            'evidencias',
        ]);

        return Inertia::render('Reportes/Show', [
            'reporte' => $reporte,
        ]);
    }

    public function edit(Reporte $reporte): Response
    {
        $reporte->load(['evidencias']);

        return Inertia::render('Reportes/Edit', [
            'reporte' => $reporte,
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function update(UpdateReporteRequest $request, Reporte $reporte): RedirectResponse
    {
        DB::transaction(function () use ($request, $reporte) {
            $reporte->update($request->safe()->except([
                'evidencias_a_eliminar',
                'evidencias_nuevas',
            ]));

            foreach ((array) $request->input('evidencias_a_eliminar', []) as $evidenciaId) {
                $evidencia = Evidencia::where('reporte_id', $reporte->id)
                    ->whereKey($evidenciaId)
                    ->first();

                if ($evidencia) {
                    Storage::disk('public')->delete($evidencia->imagen);
                    $evidencia->delete();
                }
            }

            foreach ($request->file('evidencias_nuevas', []) as $idx => $evidencia) {
                $path = $evidencia['imagen']->store('evidencias', 'public');
                $reporte->evidencias()->create([
                    'imagen' => $path,
                    'descripcion' => $request->input("evidencias_nuevas.$idx.descripcion"),
                ]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reporte actualizado.')]);

        return to_route('reportes.show', $reporte);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $filename = 'reportes-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(
            new ReportesExport($validated['desde'] ?? null, $validated['hasta'] ?? null),
            $filename,
        );
    }

    public function destroy(Reporte $reporte): RedirectResponse
    {
        DB::transaction(function () use ($reporte) {
            foreach ($reporte->evidencias as $evidencia) {
                Storage::disk('public')->delete($evidencia->imagen);
            }

            $reporte->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reporte eliminado.')]);

        return to_route('reportes.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogos(): array
    {
        return [
            'comedores' => Comedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'servicios' => Servicio::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'tiposIncidente' => TipoIncidente::with(['clasificaciones' => fn ($q) => $q->where('activo', true)->orderBy('nombre')])
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'analisisCausas' => AnalisisCausa::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'criticidades' => Criticidad::options(),
        ];
    }
}
