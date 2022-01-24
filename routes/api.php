<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Password\PasswordController;
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

Route::group([ 'middleware' => 'api', 'prefix' => 'auth' ], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function($router) {
    Route::group(['prefix' => 'auth'], function($router) {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::group(['prefix' => 'passwords'], function($router) {
        Route::get('/', [PasswordController::class, 'list']);
        Route::post('/', [PasswordController::class, 'create']);
        Route::get('/{id}', [PasswordController::class, 'show']);
        Route::put('/{id}', [PasswordController::class, 'update']);
        Route::delete('/{id}', [PasswordController::class, 'delete']);
    });
});
