<?php

use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ImagenController;
use App\Http\Controllers\Api\ImagenCategoriaController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PartidaController;
use App\Http\Controllers\Api\PartidaImagenController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SalaController;
use App\Http\Controllers\Api\SalaCategoriaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UsuarioPartidaController;
use App\Http\Controllers\Api\UsuarioSalaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    //usuarios
    Route::apiResource('users', UserController::class);
    Route::post('users/updateimg', [UserController::class, 'updateimg']);

    //categorias
    Route::apiResource('categorias', CategoriaController::class);

    //salas
    Route::apiResource('salas', SalaController::class);

    // Relacion N:M Sala - Categoria
    Route::apiResource('sala-categorias', SalaCategoriaController::class);

    //roles y permisos
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    Route::get('role-list', [RoleController::class, 'getList']);
    Route::get('role-permissions/{id}', [PermissionController::class, 'getRolePermissions']);
    Route::put('role-permissions', [PermissionController::class, 'updateRolePermissions']);


    //imagenes
    Route::post('imagenes/store-with-upload', [ImagenController::class, 'storeWithUpload'])->name('imagenes.store-upload');
    Route::apiResource('imagenes', ImagenController::class)->parameters(['imagenes' => 'imagen']);
    Route::get('imagenes-list', [ImagenController::class, 'getList']);
    Route::post('imagenes/{imagen}/upload', [ImagenController::class, 'uploadImage'])->name('imagenes.upload');
    Route::get('imagenes/{imagen}/media-info', [ImagenController::class, 'getMediaInfo'])->name('imagenes.media-info');
    Route::get('imagenes/{imagen}/all-media', [ImagenController::class, 'getAllMedia'])->name('imagenes.all-media');

    //usuario-partida
    Route::get('usuario-partidas', [UsuarioPartidaController::class, 'index']);
    Route::post('usuario-partidas', [UsuarioPartidaController::class, 'store']);
    Route::get('usuario-partidas/{idPartida}', [UsuarioPartidaController::class, 'show']);
    Route::put('usuario-partidas/{idPartida}', [UsuarioPartidaController::class, 'update']);
    Route::delete('usuario-partidas/{idPartida}', [UsuarioPartidaController::class, 'destroy']);

    //partida-imagen
    Route::get('partida-imagenes', [PartidaImagenController::class, 'index']);
    Route::post('partida-imagenes', [PartidaImagenController::class, 'store']);
    Route::get('partida-imagenes/{idPartida}', [PartidaImagenController::class, 'show']);
    Route::delete('partida-imagenes/{idPartida}/{idImagen}', [PartidaImagenController::class, 'destroy']);

    //imagen-categoria
    Route::get('imagen-categorias', [ImagenCategoriaController::class, 'index']);
    Route::post('imagen-categorias', [ImagenCategoriaController::class, 'store']);
    Route::get('imagen-categorias/{idImagen}', [ImagenCategoriaController::class, 'show']);
    Route::delete('imagen-categorias/{idImagen}/{idCategoria}', [ImagenCategoriaController::class, 'destroy']);

    //usuario-sala
    Route::get('usuario-salas', [UsuarioSalaController::class, 'index']);
    Route::post('usuario-salas', [UsuarioSalaController::class, 'store']);
    Route::get('usuario-salas/{idSala}', [UsuarioSalaController::class, 'show']);
    Route::delete('usuario-salas/{idSala}', [UsuarioSalaController::class, 'destroy']);

    //partidas
    Route::post('partidas/registrar-resultado', [PartidaController::class, 'storeResult']);
    Route::apiResource('partidas', PartidaController::class);

    //perfil
    Route::get('user', [ProfileController::class, 'user']);
    Route::get('user/signin', [ProfileController::class, 'user']);
    Route::get('user/stats', [ProfileController::class, 'stats']);
    Route::put('user', [ProfileController::class, 'update']);


    //permiso
    Route::get('abilities', function (Request $request) {
        return $request->user()->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    });
});

