<?php

use App\Http\Controllers\Empresas;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('empresas')
    ->name('empresas.')
    ->group(function () {
        Route::get('/', [Empresas::class, 'index'])->middleware('empresa.access:viewAny')->name('index');

        Route::get('/crear', [Empresas::class, 'create'])->middleware('empresa.access:create')->name('create');

        Route::post('/', [Empresas::class, 'store'])->middleware('empresa.access:store')->name('store');

        Route::get('/{empresa}', function ($empresa) {
            return "Detalle empresa {$empresa}";
        })->middleware('empresa.access:view')->name('show');

        Route::get('/{empresa}/editar', function ($empresa) {
            return "Formulario editar empresa {$empresa}";
        })->middleware('empresa.access:edit')->name('edit');

        Route::put('/{empresa}', function ($empresa) {
            return "Actualizar empresa {$empresa}";
        })->middleware('empresa.access:update')->name('update');

        Route::delete('/{empresa}', function ($empresa) {
            return "Eliminar empresa {$empresa}";
        })->middleware('empresa.access:delete')->name('destroy');

        Route::get('/{empresa}/contactos', function ($empresa) {
            return "Historial de contactos de empresa {$empresa}";
        })->middleware('empresa.access:contacts')->name('contactos.index');

        Route::post('/{empresa}/contactos', function ($empresa) {
            return "Guardar contacto de empresa {$empresa}";
        })->middleware('empresa.access:contacts')->name('contactos.store');

        Route::get('/exportar', function () {
            return 'Exportar empresas';
        })->middleware('empresa.access:export')->name('export');
    });