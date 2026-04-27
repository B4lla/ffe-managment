<?php

use App\Http\Controllers\Empresas;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('empresas')
    ->name('empresas.')
    ->group(function () {
        Route::get('/', [Empresas::class, 'index'])->name('index');

        Route::get('/crear', [Empresas::class, 'create'])->name('create');

        Route::post('/', [Empresas::class, 'store'])->name('store');

        Route::get('/{empresa}', function ($empresa) {
            return "Detalle empresa {$empresa}";
        })->name('show');

        Route::get('/{empresa}/editar', function ($empresa) {
            return "Formulario editar empresa {$empresa}";
        })->name('edit');

        Route::put('/{empresa}', function ($empresa) {
            return "Actualizar empresa {$empresa}";
        })->name('update');

        Route::delete('/{empresa}', function ($empresa) {
            return "Eliminar empresa {$empresa}";
        })->name('destroy');

        Route::get('/{empresa}/contactos', function ($empresa) {
            return "Historial de contactos de empresa {$empresa}";
        })->name('contactos.index');

        Route::post('/{empresa}/contactos', function ($empresa) {
            return "Guardar contacto de empresa {$empresa}";
        })->name('contactos.store');

        Route::get('/exportar', function () {
            return 'Exportar empresas';
        })->name('export');
    });