<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::apiResource('/registro', App\Http\Controllers\RegistroController::class);
Route::post('/deposito', [App\Http\Controllers\RegistroController::class, 'store']);
Route::get('/balance', [App\Http\Controllers\RegistroController::class, 'show']);
Route::post('/retiro', [App\Http\Controllers\RegistroController::class, 'retiro']);