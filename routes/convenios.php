<?php

use App\Http\Controllers\Convenios;
use App\Http\Controllers\DashboardController;
use App\Models\Convenio;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/prueba', [DashboardController::class, 'index'])->name('prueba');
    Route::get('/prueba2', [DashboardController::class, 'index'])->name('prueba2');
});

Route::get('/convenios/insertar', [Convenios::class, 'create'])
    ->middleware(['auth', 'verified', 'convenio.access:create'])
    ->name('convenios.insertar');

Route::get('/convenios/{id}', function ($id) {
    $convenio = Convenio::with('empresa')->findOrFail($id);
    return view('convenios.show', compact('convenio'));
})->whereNumber('id')->middleware(['auth', 'verified', 'convenio.access:view'])->name('convenios.show');

Route::get('/convenios/{id}/datos', function ($id) {
    $convenio = Convenio::with('empresa')->findOrFail($id);
    return view('convenios.actions.meter_datos', compact('convenio'));
})->middleware(['auth', 'verified', 'convenio.access:editInitial'])->name('convenios.datos');

Route::get('/convenios/{id}/generar-pdf', function ($id) {
    $convenio = Convenio::with('empresa')->findOrFail($id);
    return view('convenios.actions.generar_pdf', compact('convenio'));
})->middleware(['auth', 'verified', 'convenio.access:generatePdf'])->name('convenios.generar_pdf');

Route::get('/convenios/{id}/firmar-empresa', function ($id) {
    $convenio = Convenio::with('empresa')->findOrFail($id);
    return view('convenios.actions.descargar_firmar_empresa', compact('convenio'));
})->middleware(['auth', 'verified', 'convenio.access:firmEmpresa'])->name('convenios.firmar_empresa');

Route::get('/convenios/{id}/validar-firma', function ($id) {
    $convenio = Convenio::with('empresa')->findOrFail($id);
    return view('convenios.actions.validar_firma', compact('convenio'));
})->middleware(['auth', 'verified', 'convenio.access:validateSignature'])->name('convenios.validar_firma');

Route::get('/convenios/{id}/firmar-centro', function ($id) {
    $convenio = Convenio::with('empresa')->findOrFail($id);
    return view('convenios.actions.firmar_centro', compact('convenio'));
})->middleware(['auth', 'verified', 'convenio.access:signCenter'])->name('convenios.firmar_centro');

Route::get('/convenios/{id}/descargar-firmado', function ($id) {
    $convenio = Convenio::with('empresa')->findOrFail($id);
    return view('convenios.actions.descargar_firmado', compact('convenio'));
})->middleware(['auth', 'verified', 'convenio.access:downloadFinal'])->name('convenios.descargar_firmado');