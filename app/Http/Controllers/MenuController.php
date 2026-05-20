<?php

namespace App\Http\Controllers;

use App\Exports\MenusExport;
use App\Http\Requests\Menus\StoreMenuRequest;
use App\Http\Requests\Menus\UpdateMenuRequest;
use App\Models\Componente;
use App\Models\Menu;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MenuController extends Controller
{
    public function index(Request $request): Response
    {
        $menus = Menu::query()
            ->with([
                'servicio:id,nombre',
                'componente:id,nombre,unidad',
                'reportadoPor:id,name',
            ])
            ->latest('fecha')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Menus/Index', [
            'menus' => $menus,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Menus/Create', [
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $validated = $request->safe()->all();

        $menu = Menu::create([
            ...$validated,
            'dias_prevision' => $this->calcularDiasPrevision(
                $validated['fecha_solicitud'],
                $validated['fecha_cambio'],
            ),
            'reportado_por_id' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cambio de menú registrado.')]);

        if ($request->boolean('crear_otro')) {
            return to_route('menus.create');
        }

        return to_route('menus.show', $menu);
    }

    public function show(Menu $menu): Response
    {
        $menu->load([
            'servicio',
            'componente',
            'reportadoPor:id,name,email',
        ]);

        return Inertia::render('Menus/Show', [
            'menu' => $menu,
        ]);
    }

    public function edit(Menu $menu): Response
    {
        return Inertia::render('Menus/Edit', [
            'menu' => $menu,
            'catalogos' => $this->catalogos(),
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $validated = $request->safe()->all();

        $menu->update([
            ...$validated,
            'dias_prevision' => $this->calcularDiasPrevision(
                $validated['fecha_solicitud'],
                $validated['fecha_cambio'],
            ),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cambio de menú actualizado.')]);

        return to_route('menus.show', $menu);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $filename = 'menus-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(
            new MenusExport($validated['desde'] ?? null, $validated['hasta'] ?? null),
            $filename,
        );
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Cambio de menú eliminado.')]);

        return to_route('menus.index');
    }

    private function calcularDiasPrevision(string $solicitud, string $cambio): int
    {
        return (int) CarbonImmutable::parse($solicitud)
            ->startOfDay()
            ->diffInDays(CarbonImmutable::parse($cambio)->startOfDay());
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogos(): array
    {
        return [
            'servicios' => Servicio::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'componentes' => Componente::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'unidad']),
        ];
    }
}
