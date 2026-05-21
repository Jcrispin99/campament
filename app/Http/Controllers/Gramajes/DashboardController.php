<?php

namespace App\Http\Controllers\Gramajes;

use App\Enums\EstatusGramaje;
use App\Http\Controllers\Controller;
use App\Models\Gramaje;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $hoy = CarbonImmutable::now('America/Lima')->startOfDay();
        $desde = isset($validated['desde']) ? CarbonImmutable::parse($validated['desde']) : null;
        $hasta = isset($validated['hasta']) ? CarbonImmutable::parse($validated['hasta']) : null;

        if (! $desde && ! $hasta) {
            $desde = $hoy->subDays(30);
            $hasta = $hoy;
        }

        $base = Gramaje::query()
            ->when($desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $hasta));

        return Inertia::render('Gramajes/Dashboard', [
            'filtros' => [
                'desde' => $desde?->toDateString(),
                'hasta' => $hasta?->toDateString(),
            ],
            'kpis' => $this->calcularKpis($base),
            'porComedor' => $this->agruparPorCatalogo($base, 'comedores', 'comedor_id'),
            'porServicio' => $this->agruparPorCatalogo($base, 'servicios', 'servicio_id'),
            'porPlato' => $this->agruparPorCatalogo($base, 'platos', 'plato_id'),
            'porComponente' => $this->topComponentes($base),
            'porSemana' => $this->tendenciaSemanal($base),
            'estatusDistribucion' => $this->estatusDistribucion($base),
            'topDesviaciones' => $this->topDesviaciones($base),
            'ultimasInconformes' => $this->ultimasInconformes($base),
        ]);
    }

    /**
     * @return array{total: int, conformes: int, inconformes: int, variacionPromedio: float, totalMedidas: int}
     */
    private function calcularKpis(Builder $base): array
    {
        $stats = (clone $base)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN estatus = 'CONFORME' THEN 1 ELSE 0 END) AS conformes")
            ->selectRaw("SUM(CASE WHEN estatus = 'INCONFORME' THEN 1 ELSE 0 END) AS inconformes")
            ->selectRaw('AVG(variacion_pct) AS variacion_promedio')
            ->selectRaw('SUM(cantidad_muestreada) AS total_medidas')
            ->first();

        return [
            'total' => (int) ($stats?->total ?? 0),
            'conformes' => (int) ($stats?->conformes ?? 0),
            'inconformes' => (int) ($stats?->inconformes ?? 0),
            'variacionPromedio' => round((float) ($stats?->variacion_promedio ?? 0), 2),
            'totalMedidas' => (int) ($stats?->total_medidas ?? 0),
        ];
    }

    /**
     * @return list<array{nombre: string, total: int}>
     */
    private function agruparPorCatalogo(Builder $base, string $tabla, string $fk): array
    {
        return (clone $base)
            ->join($tabla, "{$tabla}.id", '=', "gramajes.{$fk}")
            ->groupBy("{$tabla}.id", "{$tabla}.nombre")
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                "{$tabla}.nombre AS nombre",
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => ['nombre' => $row->nombre, 'total' => (int) $row->total])
            ->all();
    }

    /**
     * @return list<array{nombre: string, total: int, conformes: int, inconformes: int}>
     */
    private function topComponentes(Builder $base): array
    {
        return (clone $base)
            ->join('componentes', 'componentes.id', '=', 'gramajes.componente_id')
            ->groupBy('componentes.id', 'componentes.nombre')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(10)
            ->get([
                'componentes.nombre AS nombre',
                DB::raw('COUNT(*) AS total'),
                DB::raw("SUM(CASE WHEN gramajes.estatus = 'CONFORME' THEN 1 ELSE 0 END) AS conformes"),
                DB::raw("SUM(CASE WHEN gramajes.estatus = 'INCONFORME' THEN 1 ELSE 0 END) AS inconformes"),
            ])
            ->map(fn ($row) => [
                'nombre' => $row->nombre,
                'total' => (int) $row->total,
                'conformes' => (int) $row->conformes,
                'inconformes' => (int) $row->inconformes,
            ])
            ->all();
    }

    /**
     * @return list<array{semana: int, total: int}>
     */
    private function tendenciaSemanal(Builder $base): array
    {
        return (clone $base)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get([
                'semana',
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => ['semana' => (int) $row->semana, 'total' => (int) $row->total])
            ->all();
    }

    /**
     * @return array{conformes: int, inconformes: int}
     */
    private function estatusDistribucion(Builder $base): array
    {
        $row = (clone $base)
            ->selectRaw("SUM(CASE WHEN estatus = 'CONFORME' THEN 1 ELSE 0 END) AS conformes")
            ->selectRaw("SUM(CASE WHEN estatus = 'INCONFORME' THEN 1 ELSE 0 END) AS inconformes")
            ->first();

        return [
            'conformes' => (int) ($row?->conformes ?? 0),
            'inconformes' => (int) ($row?->inconformes ?? 0),
        ];
    }

    /**
     * @return list<array{id: int, fecha: string, componente: ?string, variacion: float}>
     */
    private function topDesviaciones(Builder $base): array
    {
        return (clone $base)
            ->with(['componente:id,nombre'])
            ->orderByRaw('ABS(variacion_pct - 100) DESC')
            ->limit(10)
            ->get(['id', 'fecha', 'componente_id', 'variacion_pct'])
            ->map(fn (Gramaje $g) => [
                'id' => $g->id,
                'fecha' => $g->fecha->toDateString(),
                'componente' => $g->componente?->nombre,
                'variacion' => (float) $g->variacion_pct,
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, fecha: string, comedor: ?string, servicio: ?string, componente: ?string, esperado: float, promedio: float, variacion: float, cantidad: int}>
     */
    private function ultimasInconformes(Builder $base): array
    {
        return (clone $base)
            ->with([
                'comedor:id,nombre',
                'servicio:id,nombre',
                'componente:id,nombre',
            ])
            ->where('estatus', EstatusGramaje::Inconforme->value)
            ->latest('fecha')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Gramaje $g) => [
                'id' => $g->id,
                'fecha' => $g->fecha->toDateString(),
                'comedor' => $g->comedor?->nombre,
                'servicio' => $g->servicio?->nombre,
                'componente' => $g->componente?->nombre,
                'esperado' => (float) $g->gramaje_esperado,
                'promedio' => (float) $g->peso_promedio,
                'variacion' => (float) $g->variacion_pct,
                'cantidad' => $g->cantidad_muestreada,
            ])
            ->all();
    }
}
