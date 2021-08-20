<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for yo ur application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::apiResource('/registro', App\Http\Controllers\RegistroController::class);
Route::post('/deposito', [App\Http\Controllers\RegistroController::class, 'deposito']);
Route::get('/balance/{id}', [App\Http\Controllers\RegistroController::class, 'balance']);
Route::post('/retiro', [App\Http\Controllers\RegistroController::class, 'retiro']);
Route::post('/crear/{id}', [App\Http\Controllers\RegistroController::class, 'crear']);
Route::post('/transferencia', [App\Http\Controllers\RegistroController::class, 'transferencia']);
Route::get('/mail', [App\Http\Controllers\RegistroController::class, 'mail2']);
Route::get('/delete', [App\Http\Controllers\RegistroController::class, 'deleteAll']);