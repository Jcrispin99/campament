<?php

namespace App\Http\Controllers\MateriasPrimas;

use App\Enums\ConformidadMp;
use App\Http\Controllers\Controller;
use App\Models\MateriaPrima;
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

        $base = MateriaPrima::query()
            ->when($desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $hasta));

        return Inertia::render('MateriasPrimas/Dashboard', [
            'filtros' => [
                'desde' => $desde?->toDateString(),
                'hasta' => $hasta?->toDateString(),
            ],
            'kpis' => $this->calcularKpis($base),
            'porTipoProducto' => $this->agruparPorCatalogo($base, 'tipos_producto', 'tipo_producto_id'),
            'porProveedor' => $this->agruparPorCatalogo($base, 'proveedores', 'proveedor_id'),
            'porOrigen' => $this->agruparPorCatalogo($base, 'origenes', 'origen_id'),
            'conformidadDistribucion' => $this->conformidadDistribucion($base),
            'porSemana' => $this->tendenciaSemanal($base),
            'topAcciones' => $this->topTextos($base, 'accion_realizada'),
            'topProductosAfectados' => $this->topTextos($base, 'productos_afectados'),
            'ultimasNc' => $this->ultimasNoConformidades($base),
        ]);
    }

    /**
     * @return array{total: int, conformes: int, conNc: int, conformidadMp: int, conformidadDocumentacion: int, conformidadVehiculo: int}
     */
    private function calcularKpis(Builder $base): array
    {
        $stats = (clone $base)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN conformidad_mp = 'CONFORME' AND conformidad_documentacion = 'CONFORME' AND conformidad_vehiculo = 'CONFORME' THEN 1 ELSE 0 END) AS conformes")
            ->selectRaw("SUM(CASE WHEN conformidad_mp = 'CONFORME' THEN 1 ELSE 0 END) AS conf_mp")
            ->selectRaw("SUM(CASE WHEN conformidad_documentacion = 'CONFORME' THEN 1 ELSE 0 END) AS conf_doc")
            ->selectRaw("SUM(CASE WHEN conformidad_vehiculo = 'CONFORME' THEN 1 ELSE 0 END) AS conf_veh")
            ->first();

        $total = (int) ($stats?->total ?? 0);
        $conformes = (int) ($stats?->conformes ?? 0);

        return [
            'total' => $total,
            'conformes' => $conformes,
            'conNc' => $total - $conformes,
            'conformidadMp' => (int) ($stats?->conf_mp ?? 0),
            'conformidadDocumentacion' => (int) ($stats?->conf_doc ?? 0),
            'conformidadVehiculo' => (int) ($stats?->conf_veh ?? 0),
        ];
    }

    /**
     * @return list<array{nombre: string, total: int}>
     */
    private function agruparPorCatalogo(Builder $base, string $tabla, string $fk): array
    {
        return (clone $base)
            ->join($tabla, "{$tabla}.id", '=', "materias_primas.{$fk}")
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
     * @return array{mp: array{conformes: int, noConformes: int}, documentacion: array{conformes: int, noConformes: int}, vehiculo: array{conformes: int, noConformes: int}}
     */
    private function conformidadDistribucion(Builder $base): array
    {
        $row = (clone $base)
            ->selectRaw("SUM(CASE WHEN conformidad_mp = 'CONFORME' THEN 1 ELSE 0 END) AS mp_c")
            ->selectRaw("SUM(CASE WHEN conformidad_mp = 'NO_CONFORME' THEN 1 ELSE 0 END) AS mp_nc")
            ->selectRaw("SUM(CASE WHEN conformidad_documentacion = 'CONFORME' THEN 1 ELSE 0 END) AS doc_c")
            ->selectRaw("SUM(CASE WHEN conformidad_documentacion = 'NO_CONFORME' THEN 1 ELSE 0 END) AS doc_nc")
            ->selectRaw("SUM(CASE WHEN conformidad_vehiculo = 'CONFORME' THEN 1 ELSE 0 END) AS veh_c")
            ->selectRaw("SUM(CASE WHEN conformidad_vehiculo = 'NO_CONFORME' THEN 1 ELSE 0 END) AS veh_nc")
            ->first();

        return [
            'mp' => [
                'conformes' => (int) ($row?->mp_c ?? 0),
                'noConformes' => (int) ($row?->mp_nc ?? 0),
            ],
            'documentacion' => [
                'conformes' => (int) ($row?->doc_c ?? 0),
                'noConformes' => (int) ($row?->doc_nc ?? 0),
            ],
            'vehiculo' => [
                'conformes' => (int) ($row?->veh_c ?? 0),
                'noConformes' => (int) ($row?->veh_nc ?? 0),
            ],
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
     * @return list<array{id: int, fecha: string, tipoProducto: ?string, proveedor: ?string, origen: ?string, causa: string, accion: string, productos: string, ncEn: list<string>}>
     */
    private function ultimasNoConformidades(Builder $base): array
    {
        return (clone $base)
            ->with([
                'tipoProducto:id,nombre',
                'proveedor:id,nombre',
                'origen:id,nombre',
            ])
            ->where(function (Builder $q) {
                $q->where('conformidad_mp', ConformidadMp::NoConforme->value)
                    ->orWhere('conformidad_documentacion', ConformidadMp::NoConforme->value)
                    ->orWhere('conformidad_vehiculo', ConformidadMp::NoConforme->value);
            })
            ->latest('fecha')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function (MateriaPrima $mp) {
                $ncEn = [];
                if ($mp->conformidad_mp === ConformidadMp::NoConforme) {
                    $ncEn[] = 'MP';
                }
                if ($mp->conformidad_documentacion === ConformidadMp::NoConforme) {
                    $ncEn[] = 'Documentación';
                }
                if ($mp->conformidad_vehiculo === ConformidadMp::NoConforme) {
                    $ncEn[] = 'Vehículo';
                }

                return [
                    'id' => $mp->id,
                    'fecha' => $mp->fecha->toDateString(),
                    'tipoProducto' => $mp->tipoProducto?->nombre,
                    'proveedor' => $mp->proveedor?->nombre,
                    'origen' => $mp->origen?->nombre,
                    'causa' => $mp->causa_nc_observacion,
                    'accion' => $mp->accion_realizada,
                    'productos' => $mp->productos_afectados,
                    'ncEn' => $ncEn,
                ];
            })
            ->all();
    }
}
