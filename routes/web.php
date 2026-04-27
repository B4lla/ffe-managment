<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Usuarios;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/usuarios/crear', [Usuarios::class, 'create'])
        ->middleware('verified')
        ->name('usuarios.create');
    Route::post('/usuarios', [Usuarios::class, 'store'])
        ->middleware('verified')
        ->name('usuarios.store');
    Route::get('/usuarios', [Usuarios::class, 'index'])
        ->middleware('verified')
        ->name('usuarios.index');
});



require_once __DIR__.'/convenios.php';
require_once __DIR__.'/empresas.php';
require_once __DIR__.'/auth.php';
