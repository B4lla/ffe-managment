<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/prueba', [DashboardController::class, 'index'])->name('prueba');
    Route::get('/prueba2', [DashboardController::class, 'index'])->name('prueba2');
});

Route::get('/convenios/{id}', function () {
    return redirect()->route('prueba');
})->middleware(['auth', 'verified'])->name('convenios.show');