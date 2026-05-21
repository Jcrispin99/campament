<?php

namespace App\Http\Controllers\Menus;

use App\Http\Controllers\Controller;
use App\Models\Menu;
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

        $base = Menu::query()
            ->when($desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $hasta));

        return Inertia::render('Menus/Dashboard', [
            'filtros' => [
                'desde' => $desde?->toDateString(),
                'hasta' => $hasta?->toDateString(),
            ],
            'kpis' => $this->calcularKpis($base),
            'porServicio' => $this->agruparPorCatalogo($base, 'servicios', 'servicio_id'),
            'porComponente' => $this->agruparPorCatalogo($base, 'componentes', 'componente_id'),
            'porSemana' => $this->tendenciaSemanal($base),
            'distribucionPrevision' => $this->distribucionPrevision($base),
            'conformidadDistribucion' => $this->conformidadDistribucion($base),
            'topMotivos' => $this->topTextos($base, 'motivo'),
            'topAnalisis' => $this->topTextos($base, 'analisis'),
            'cambiosUrgentes' => $this->cambiosUrgentes($base),
        ]);
    }

    /**
     * @return array{total: int, previsionPromedio: float, conformesAprox: int, inconformesAprox: int, sinPrevision: int}
     */
    private function calcularKpis(Builder $base): array
    {
        $stats = (clone $base)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('AVG(dias_prevision) AS prevision_promedio')
            ->selectRaw("SUM(CASE WHEN LOWER(conformidad) LIKE '%conforme%' AND LOWER(conformidad) NOT LIKE '%inconforme%' THEN 1 ELSE 0 END) AS conformes_aprox")
            ->selectRaw("SUM(CASE WHEN LOWER(conformidad) LIKE '%inconforme%' THEN 1 ELSE 0 END) AS inconformes_aprox")
            ->selectRaw('SUM(CASE WHEN dias_prevision < 3 THEN 1 ELSE 0 END) AS sin_prevision')
            ->first();

        return [
            'total' => (int) ($stats?->total ?? 0),
            'previsionPromedio' => round((float) ($stats?->prevision_promedio ?? 0), 1),
            'conformesAprox' => (int) ($stats?->conformes_aprox ?? 0),
            'inconformesAprox' => (int) ($stats?->inconformes_aprox ?? 0),
            'sinPrevision' => (int) ($stats?->sin_prevision ?? 0),
        ];
    }

    /**
     * @return list<array{nombre: string, total: int}>
     */
    private function agruparPorCatalogo(Builder $base, string $tabla, string $fk): array
    {
        return (clone $base)
            ->join($tabla, "{$tabla}.id", '=', "menus.{$fk}")
            ->groupBy("{$tabla}.id", "{$tabla}.nombre")
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(10)
            ->get([
                "{$tabla}.nombre AS nombre",
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => ['nombre' => $row->nombre, 'total' => (int) $row->total])
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
     * @return list<array{rango: string, total: int}>
     */
    private function distribucionPrevision(Builder $base): array
    {
        $row = (clone $base)
            ->selectRaw('SUM(CASE WHEN dias_prevision < 3 THEN 1 ELSE 0 END) AS critico')
            ->selectRaw('SUM(CASE WHEN dias_prevision BETWEEN 3 AND 4 THEN 1 ELSE 0 END) AS aceptable')
            ->selectRaw('SUM(CASE WHEN dias_prevision >= 5 THEN 1 ELSE 0 END) AS bueno')
            ->first();

        return [
            ['rango' => 'Menos de 3 días', 'total' => (int) ($row?->critico ?? 0)],
            ['rango' => '3-4 días', 'total' => (int) ($row?->aceptable ?? 0)],
            ['rango' => '5 o más días', 'total' => (int) ($row?->bueno ?? 0)],
        ];
    }

    /**
     * @return list<array{texto: string, total: int}>
     */
    private function conformidadDistribucion(Builder $base): array
    {
        return (clone $base)
            ->whereNotNull('conformidad')
            ->where('conformidad', '!=', '')
            ->groupBy('conformidad')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                'conformidad AS texto',
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => ['texto' => $row->texto, 'total' => (int) $row->total])
            ->all();
    }

    /**
     * @return list<array{texto: string, total: int}>
     */
    private function topTextos(Builder $base, string $columna): array
    {
        return (clone $base)
            ->whereNotNull($columna)
            ->where($columna, '!=', '')
            ->groupBy($columna)
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(8)
            ->get([
                "{$columna} AS texto",
                DB::raw('COUNT(*) AS total'),
            ])
            ->map(fn ($row) => ['texto' => $row->texto, 'total' => (int) $row->total])
            ->all();
    }

    /**
     * @return list<array{id: int, fecha: string, servicio: ?string, componente: ?string, programado: string, propuesta: string, motivo: string, diasPrevision: int, conformidad: string}>
     */
    private function cambiosUrgentes(Builder $base): array
    {
        return (clone $base)
            ->with([
                'servicio:id,nombre',
                'componente:id,nombre',
            ])
            ->where('dias_prevision', '<', 3)
            ->latest('fecha')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Menu $m) => [
                'id' => $m->id,
                'fecha' => $m->fecha->toDateString(),
                'servicio' => $m->servicio?->nombre,
                'componente' => $m->componente?->nombre,
                'programado' => $m->programado,
                'propuesta' => $m->propuesta,
                'motivo' => $m->motivo,
                'diasPrevision' => $m->dias_prevision,
                'conformidad' => $m->conformidad,
            ])
            ->all();
    }
}
