<?php

use App\Http\Controllers\Api\AdminStatsController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\CategoryPublicController;
use App\Http\Controllers\Api\ImagenCategoriaController;
use App\Http\Controllers\Api\ImagenController;
use App\Http\Controllers\Api\PartidaController;
use App\Http\Controllers\Api\PartidaImagenController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RankingPublicController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SalaCategoriaController;
use App\Http\Controllers\Api\SalaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UsuarioPartidaController;
use App\Http\Controllers\Api\UsuarioSalaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->name('public.')->middleware('throttle:api')->group(function () {
    Route::get('categories', [CategoryPublicController::class, 'index'])->name('categories.index');

    Route::get('rankings', [RankingPublicController::class, 'index'])->name('rankings.index');

    Route::get('rankings/category/{categoria?}', [RankingPublicController::class, 'category'])
        ->whereNumber('categoria')
        ->name('rankings.category');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('admin/player-stats/reset', [AdminStatsController::class, 'resetAll'])
        ->middleware('role:admin');

    Route::apiResource('users', UserController::class);
    Route::post('users/updateimg', [UserController::class, 'updateimg']);

    Route::apiResource('categorias', CategoriaController::class)
        ->middlewareFor(['store', 'update', 'destroy'], 'role:admin');
    Route::get('categorias-list', [CategoriaController::class, 'getList']);

    Route::apiResource('salas', SalaController::class);

    Route::get('sala-categorias', [SalaCategoriaController::class, 'index']);
    Route::post('sala-categorias', [SalaCategoriaController::class, 'store']);
    Route::get('sala-categorias/{id_sala}/{id_categoria}', [SalaCategoriaController::class, 'show'])
        ->whereNumber('id_sala')
        ->whereNumber('id_categoria');
    Route::put('sala-categorias/{id_sala}/{id_categoria}', [SalaCategoriaController::class, 'update'])
        ->whereNumber('id_sala')
        ->whereNumber('id_categoria');
    Route::delete('sala-categorias/{id_sala}/{id_categoria}', [SalaCategoriaController::class, 'destroy'])
        ->whereNumber('id_sala')
        ->whereNumber('id_categoria');

    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::get('role-list', [RoleController::class, 'getList']);
    Route::get('role-permissions/{id}', [PermissionController::class, 'getRolePermissions'])
        ->whereNumber('id');

    Route::post('imagenes/store-with-upload', [ImagenController::class, 'storeWithUpload'])->name('imagenes.store-upload');
    Route::apiResource('imagenes', ImagenController::class)->parameters(['imagenes' => 'imagen']);
    Route::get('imagenes-list', [ImagenController::class, 'getList']);
    Route::post('imagenes/{imagen}/upload', [ImagenController::class, 'uploadImage'])->name('imagenes.upload');
    Route::get('imagenes/{imagen}/media-info', [ImagenController::class, 'getMediaInfo'])->name('imagenes.media-info');
    Route::get('imagenes/{imagen}/all-media', [ImagenController::class, 'getAllMedia'])->name('imagenes.all-media');

    Route::get('usuario-partidas', [UsuarioPartidaController::class, 'index']);
    Route::post('usuario-partidas', [UsuarioPartidaController::class, 'store']);
    Route::post('usuario-partidas/finalizar', [UsuarioPartidaController::class, 'finish']);
    Route::get('usuario-partidas/{idPartida}', [UsuarioPartidaController::class, 'show'])
        ->whereNumber('idPartida');
    Route::put('usuario-partidas/{idPartida}', [UsuarioPartidaController::class, 'update'])
        ->whereNumber('idPartida');
    Route::delete('usuario-partidas/{idPartida}', [UsuarioPartidaController::class, 'destroy'])
        ->whereNumber('idPartida');

    Route::get('partida-imagenes', [PartidaImagenController::class, 'index']);
    Route::post('partida-imagenes', [PartidaImagenController::class, 'store']);
    Route::get('partida-imagenes/{idPartida}', [PartidaImagenController::class, 'show'])
        ->whereNumber('idPartida');
    Route::delete('partida-imagenes/{idPartida}/{idImagen}', [PartidaImagenController::class, 'destroy'])
        ->whereNumber('idPartida')
        ->whereNumber('idImagen');

    Route::get('imagen-categorias', [ImagenCategoriaController::class, 'index']);
    Route::post('imagen-categorias', [ImagenCategoriaController::class, 'store']);
    Route::get('imagen-categorias/{idImagen}', [ImagenCategoriaController::class, 'show'])
        ->whereNumber('idImagen');
    Route::delete('imagen-categorias/{idImagen}/{idCategoria}', [ImagenCategoriaController::class, 'destroy'])
        ->whereNumber('idImagen')
        ->whereNumber('idCategoria');

    Route::get('usuario-salas', [UsuarioSalaController::class, 'index']);
    Route::post('usuario-salas', [UsuarioSalaController::class, 'store']);
    Route::get('usuario-salas/{idSala}', [UsuarioSalaController::class, 'show'])
        ->whereNumber('idSala');
    Route::delete('usuario-salas/{idSala}', [UsuarioSalaController::class, 'destroy'])
        ->whereNumber('idSala');

    Route::post('partidas/registrar-resultado', [PartidaController::class, 'storeResult']);
    Route::apiResource('partidas', PartidaController::class);

    Route::get('user', [ProfileController::class, 'user']);
    Route::get('user/stats', [ProfileController::class, 'stats']);
    Route::put('user', [ProfileController::class, 'update']);

    Route::get('abilities', [ProfileController::class, 'abilities']);

    Route::patch('users/{user}/image', [UserController::class, 'updateImage']);

    Route::patch('partidas/{partida}/resultado', [PartidaController::class, 'storeResult']);
});

Route::patch('partidas/{partida}/resultado', [PartidaController::class, 'storeResult'])
    ->whereNumber('partida');
