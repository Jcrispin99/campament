<?php

use App\Http\Controllers\Catalogos\AnalisisCausaController;
use App\Http\Controllers\Catalogos\ClasificacionIncidenteController;
use App\Http\Controllers\Catalogos\ComedorController;
use App\Http\Controllers\Catalogos\ComponenteController;
use App\Http\Controllers\Catalogos\OrigenController;
use App\Http\Controllers\Catalogos\PlatoController;
use App\Http\Controllers\Catalogos\ProveedorController;
use App\Http\Controllers\Catalogos\ServicioController;
use App\Http\Controllers\Catalogos\TipoCorteController;
use App\Http\Controllers\Catalogos\TipoIncidenteController;
use App\Http\Controllers\Catalogos\TipoProductoController;
use App\Http\Controllers\GramajeController;
use App\Http\Controllers\Gramajes\DashboardController as GramajesDashboardController;
use App\Http\Controllers\MateriaPrimaController;
use App\Http\Controllers\MateriasPrimas\DashboardController as MateriasPrimasDashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Menus\DashboardController as MenusDashboardController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Reportes\DashboardController as ReportesDashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('reportes/export', [ReporteController::class, 'export'])
        ->name('reportes.export');
    Route::get('reportes/dashboard', [ReportesDashboardController::class, 'index'])
        ->name('reportes.dashboard');
    Route::resource('reportes', ReporteController::class);

    Route::get('gramajes/export', [GramajeController::class, 'export'])
        ->name('gramajes.export');
    Route::get('gramajes/dashboard', [GramajesDashboardController::class, 'index'])
        ->name('gramajes.dashboard');
    Route::resource('gramajes', GramajeController::class);

    Route::get('menus/export', [MenuController::class, 'export'])
        ->name('menus.export');
    Route::get('menus/dashboard', [MenusDashboardController::class, 'index'])
        ->name('menus.dashboard');
    Route::resource('menus', MenuController::class);

    Route::get('materias-primas/export', [MateriaPrimaController::class, 'export'])
        ->name('materias-primas.export');
    Route::get('materias-primas/dashboard', [MateriasPrimasDashboardController::class, 'index'])
        ->name('materias-primas.dashboard');
    Route::resource('materias-primas', MateriaPrimaController::class)
        ->parameters(['materias-primas' => 'materiaPrima']);

    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::resource('comedores', ComedorController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['comedores' => 'comedor']);
        Route::resource('servicios', ServicioController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::resource('tipos-incidente', TipoIncidenteController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['tipos-incidente' => 'tipoIncidente']);
        Route::resource('clasificaciones', ClasificacionIncidenteController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['clasificaciones' => 'clasificacion']);
        Route::resource('analisis-causas', AnalisisCausaController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['analisis-causas' => 'analisisCausa']);
        Route::resource('platos', PlatoController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::resource('componentes', ComponenteController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::resource('tipos-corte', TipoCorteController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['tipos-corte' => 'tipoCorte']);
        Route::resource('tipos-producto', TipoProductoController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['tipos-producto' => 'tipoProducto']);
        Route::resource('proveedores', ProveedorController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['proveedores' => 'proveedor']);
        Route::resource('origenes', OrigenController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['origenes' => 'origen']);
    });
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});

require __DIR__.'/settings.php';
