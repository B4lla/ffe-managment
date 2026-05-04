<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Usuarios;
use App\Http\Controllers\Convenios;
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
        ->middleware(['verified', 'role.access:Administrador,Coordinador FFE,Profesor tutor'])
        ->name('usuarios.create');
    Route::post('/usuarios', [Usuarios::class, 'store'])
        ->middleware(['verified', 'role.access:Administrador,Coordinador FFE,Profesor tutor'])
        ->name('usuarios.store');
    Route::get('/usuarios', [Usuarios::class, 'index'])
        ->middleware(['verified', 'role.access:Administrador'])
        ->name('usuarios.index');


    Route::get('/convenios', [Convenios::class, 'index'])
        ->middleware(['verified', 'convenio.access:viewAny'])
        ->name('convenios.index');

    Route::get('/convenios/create', [Convenios::class, 'create'])
        ->middleware(['verified', 'convenio.access:create'])
        ->name('convenios.create');
    Route::post('/convenios', [Convenios::class, 'store'])
        ->middleware(['verified', 'convenio.access:store'])
        ->name('convenios.store');
});



require __DIR__.'/Convenios.php';
require __DIR__.'/empresas.php';
require __DIR__.'/auth.php';
require __DIR__.'/tareas_pendientes.php';
