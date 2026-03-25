<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('login', [AuthenticatedSessionController::class, 'login']);
Route::post('register', [AuthenticatedSessionController::class, 'register']);
Route::post('logout', [AuthenticatedSessionController::class, 'logout']);

 


//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


use App\Http\Controllers\SalaController;

//Route::prefix('admin/salas')->group(function () {
   // Route::get('/', [SalaController::class, 'index'])->name('admin.salas.index');
   // Route::get('/create', [SalaController::class, 'create'])->name('admin.salas.create');
   // Route::post('/', [SalaController::class, 'store'])->name('admin.salas.store');
   // Route::get('/edit/{sala}', [SalaController::class, 'edit'])->name('admin.salas.edit');
   // Route::put('/{sala}', [SalaController::class, 'update'])->name('admin.salas.update');
  //  Route::delete('/{sala}', [SalaController::class, 'destroy'])->name('admin.salas.destroy');
//});


Route::view('/{any?}', 'main-view')
    ->name('dashboard')
    ->where('any', '^(?!api(?:/|$)).*');
