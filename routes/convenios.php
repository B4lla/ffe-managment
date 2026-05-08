<?php

use App\Http\Controllers\Convenios;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/prueba', [DashboardController::class, 'index'])->name('prueba');
    Route::get('/prueba2', [DashboardController::class, 'index'])->name('prueba2');
});

Route::get('/convenios/insertar', [Convenios::class, 'create'])
    ->middleware(['auth', 'verified', 'convenio.access:create'])
    ->name('convenios.insertar');

Route::post('/convenios/importar', [Convenios::class, 'importarConvenios'])
    ->middleware(['auth', 'verified', 'convenio.access:create'])
    ->name('convenios.importar');

Route::get('/convenios/{id}', [Convenios::class, 'show'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:view'])
    ->name('convenios.show');

Route::delete('/convenios/{id}', [Convenios::class, 'destroy'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:delete'])
    ->name('convenios.destroy');

Route::get('/convenios/{id}/datos', [Convenios::class, 'editInitial'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:editInitial'])
    ->name('convenios.datos');

Route::put('/convenios/{id}/datos', [Convenios::class, 'updateInitial'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:editInitial'])
    ->name('convenios.datos.update');

Route::get('/convenios/{id}/editar-tutor', [Convenios::class, 'editTutorForm'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:editInitial'])
    ->name('convenios.editar_tutor');

Route::put('/convenios/{id}/editar-tutor', [Convenios::class, 'updateTutor'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:editInitial'])
    ->name('convenios.editar_tutor.update');

Route::get('/convenios/{id}/generar-pdf', [Convenios::class, 'generatePdfForm'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:generatePdf'])
    ->name('convenios.generar_pdf');

Route::post('/convenios/{id}/generar-pdf', [Convenios::class, 'storeGeneratedPdf'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:generatePdf'])
    ->name('convenios.generar_pdf.store');

Route::get('/convenios/{id}/firmar-empresa', [Convenios::class, 'firmEmpresaForm'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:firmEmpresa'])
    ->name('convenios.firmar_empresa');

Route::post('/convenios/{id}/firmar-empresa', [Convenios::class, 'uploadFirmadoEmpresa'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:firmEmpresa'])
    ->name('convenios.firmar_empresa.store');

Route::post('/convenios/{id}/reportar-error-empresa', [Convenios::class, 'reportarErrorEmpresa'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:firmEmpresa'])
    ->name('convenios.reportar_error_empresa');

Route::get('/convenios/{id}/validar-firma', [Convenios::class, 'validarFirmaForm'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:validateSignature'])
    ->name('convenios.validar_firma');

Route::post('/convenios/{id}/validar-firma', [Convenios::class, 'validarFirmaEmpresa'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:validateSignature'])
    ->name('convenios.validar_firma.store');

Route::get('/convenios/{id}/firmar-centro', [Convenios::class, 'firmarCentroForm'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:signCenter'])
    ->name('convenios.firmar_centro');

Route::post('/convenios/{id}/firmar-centro', [Convenios::class, 'subirFirmaCentro'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:signCenter'])
    ->name('convenios.firmar_centro.store');

Route::post('/convenios/{id}/firmar-centro/rechazar', [Convenios::class, 'rechazarFirmaCentro'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:signCenter'])
    ->name('convenios.firmar_centro.rechazar');

Route::get('/convenios/{id}/descargar-firmado', [Convenios::class, 'descargarFirmadoForm'])
    ->whereNumber('id')
    ->middleware(['auth', 'verified', 'convenio.access:downloadFinal'])
    ->name('convenios.descargar_firmado');

Route::get('/convenios/{id}/documentos/{documentoId}/descargar', [Convenios::class, 'downloadDocument'])
    ->whereNumber('id')
    ->whereNumber('documentoId')
    ->middleware(['auth', 'verified', 'convenio.access:view'])
    ->name('convenios.documentos.descargar');
