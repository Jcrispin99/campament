<?php

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
