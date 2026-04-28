<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TareasPendientesController;

Route::middleware(['auth'])->group(function () {
    Route::get('/tareas_pendientes', [TareasPendientesController::class, 'index'])->name('tareas_pendientes.index');
    Route::post('/tareas_pendientes/{id}/completar', [TareasPendientesController::class, 'completar'])->name('tareas_pendientes.completar');
});
