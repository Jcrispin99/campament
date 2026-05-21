<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
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

        $base = Reporte::query()
            ->when($desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $hasta));

        return Inertia::render('Reportes/Dashboard', [
            'filtros' => [
                'desde' => $desde?->toDateString(),
                'hasta' => $hasta?->toDateString(),
            ],
            'kpis' => $this->calcularKpis($base),
            'porTipo' => $this->agruparPorTipo($base),
            'porClasificacion' => $this->topClasificaciones($base),
            'porCausa' => $this->agruparPorCausa($base),
            'porCriticidad' => $this->agruparPorCriticidad($base),
            'corregidos' => $this->corregidosVsPendientes($base),
            'porSemana' => $this->tendenciaSemanal($base),
        ]);
    }

    /**
     * @return array{total: int, criticos: int, corregidos: int, planAccion: int}
     */
    private function calcularKpis(Builder $base): array
    {
        $stats = (clone $base)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN criticidad = 'CRITICO' THEN 1 ELSE 0 END) AS criticos")
            ->selectRaw('SUM(CASE WHEN se_corrigio = 1 THEN 1 ELSE 0 END) AS corregidos')
            ->selectRaw('SUM(CASE WHEN requiere_plan_accion = 1 THEN 1 ELSE 0 END) AS plan_accion')
            ->first();

        return [
            'total' => (int) ($stats?->total ?? 0),
            'criticos' => (int) ($stats?->criticos ?? 0),
            'corregidos' => (int) ($stats?->corregidos ?? 0),
            'planAccion' => (int) ($stats?->plan_accion ?? 0),
        ];
    }

    /**
     * @return list<array{nombre: string, total: int}>
     */
    private function agruparPorTipo(Builder $base): array
    {
        return (clone $base)
            ->join('tipos_incidente', 'tipos_incidente.id', '=', 'reportes.tipo_incidente_id')
            ->groupBy('tipos_incidente.id', 'tipos_incidente.nombre')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                'tipos_incidente.nombre AS nombre',
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => ['nombre' => $row->nombre, 'total' => (int) $row->total])
            ->all();
    }

    /**
     * @return list<array{nombre: string, tipo: string, total: int}>
     */
    private function topClasificaciones(Builder $base): array
    {
        return (clone $base)
            ->join('clasificaciones_incidente', 'clasificaciones_incidente.id', '=', 'reportes.clasificacion_id')
            ->join('tipos_incidente', 'tipos_incidente.id', '=', 'clasificaciones_incidente.tipo_incidente_id')
            ->groupBy('clasificaciones_incidente.id', 'clasificaciones_incidente.nombre', 'tipos_incidente.nombre')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(10)
            ->get([
                'clasificaciones_incidente.nombre AS nombre',
                'tipos_incidente.nombre AS tipo',
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => [
                'nombre' => $row->nombre,
                'tipo' => $row->tipo,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{nombre: string, total: int}>
     */
    private function agruparPorCausa(Builder $base): array
    {
        return (clone $base)
            ->join('analisis_causas', 'analisis_causas.id', '=', 'reportes.analisis_causa_id')
            ->groupBy('analisis_causas.id', 'analisis_causas.nombre')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                'analisis_causas.nombre AS nombre',
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => ['nombre' => $row->nombre, 'total' => (int) $row->total])
            ->all();
    }

    /**
     * @return list<array{nivel: string, total: int}>
     */
    private function agruparPorCriticidad(Builder $base): array
    {
        $orden = ['LEVE' => 1, 'MODERADO' => 2, 'CRITICO' => 3];

        $rows = (clone $base)
            ->groupBy('criticidad')
            ->get([
                'criticidad AS nivel',
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => ['nivel' => $row->nivel, 'total' => (int) $row->total])
            ->all();

        usort($rows, fn ($a, $b) => $orden[$a['nivel']] <=> $orden[$b['nivel']]);

        return $rows;
    }

    /**
     * @return array{corregidos: int, pendientes: int}
     */
    private function corregidosVsPendientes(Builder $base): array
    {
        $row = (clone $base)
            ->selectRaw('SUM(CASE WHEN se_corrigio = 1 THEN 1 ELSE 0 END) AS corregidos')
            ->selectRaw('SUM(CASE WHEN se_corrigio = 0 THEN 1 ELSE 0 END) AS pendientes')
            ->first();

        return [
            'corregidos' => (int) ($row?->corregidos ?? 0),
            'pendientes' => (int) ($row?->pendientes ?? 0),
        ];
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
}
