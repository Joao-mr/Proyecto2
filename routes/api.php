<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SalaController;
use App\Http\Controllers\Api\SalaCategoriaController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    //usuarios
    Route::apiResource('users', UserController::class);
    Route::post('users/updateimg', [UserController::class, 'updateimg']);

    Route::apiResource('categories', CategoryController::class);

    //categorias
    Route::apiResource('categorias', CategoriaController::class);

    //salas
    Route::apiResource('salas', SalaController::class);

    // Relación N:M Sala - Categoría
    Route::apiResource('sala-categorias', SalaCategoriaController::class);

    //roles y permisos
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    Route::get('role-list', [RoleController::class, 'getList']);
    Route::get('role-permissions/{id}', [PermissionController::class, 'getRolePermissions']);
    Route::put('role-permissions', [PermissionController::class, 'updateRolePermissions']);


    //perfil
    Route::get('user', [ProfileController::class, 'user']);
    Route::get('user/signin', [ProfileController::class, 'user']);
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
/*
// Eliminades rutes incorrectes: getCategories, deleteCategory, updateCategory, category-list
// Route Model Binding ja aplicat a CategoryController per {category}
*/
