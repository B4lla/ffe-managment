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

        Route::get('/{empresa}', [Empresas::class, 'show'])->middleware('empresa.access:view')->name('show');

        Route::get('/{empresa}/editar', [Empresas::class, 'edit'])->middleware('empresa.access:edit')->name('edit');

        Route::put('/{empresa}', [Empresas::class, 'update'])->middleware('empresa.access:update')->name('update');

        Route::delete('/{empresa}', [Empresas::class, 'destroy'])->middleware('empresa.access:delete')->name('destroy');

        Route::get('/{empresa}/contactos', [Empresas::class, 'contactosIndex'])
            ->middleware('empresa.access:contacts')
            ->name('contactos.index');

        Route::get('/buscar', [Empresas::class, 'search'])
            ->middleware('empresa.access:viewAny')
            ->name('search');

        Route::post('/{empresa}/contactos', [Empresas::class, 'contactosStore'])
            ->middleware('empresa.access:contacts')
            ->name('contactos.store');

        Route::get('/exportar', [Empresas::class, 'export'])->middleware('empresa.access:export')->name('export');
    });