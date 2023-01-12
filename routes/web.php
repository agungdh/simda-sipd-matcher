<?php

use App\Http\Controllers\CheckController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SetController;
use App\Http\Controllers\SetDebugController;
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

Route::prefix('check')->group(function () {
    Route::get('/unit', [CheckController::class, 'unit']);
    Route::get('/unit/error', [CheckController::class, 'unitError']);
    Route::get('/rka', [CheckController::class, 'rka']);
});

Route::prefix('reset')->group(function () {
    Route::get('/rka', [ResetController::class, 'rka']);
});

Route::prefix('set')->group(function () {
    Route::get('/nonprogramkegiatan', [SetController::class, 'nonprogramkegiatan']);
    Route::prefix('debug')->group(function () {
        Route::get('/yes', [SetDebugController::class, 'yes']);
        Route::get('/no', [SetDebugController::class, 'no']);
        Route::get('/check', [SetDebugController::class, 'check']);
    });
});

Route::get('/', function () {
    return view('welcome');
});
