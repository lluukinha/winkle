<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Folder\FolderController;
use App\Http\Controllers\Note\NoteController;
use App\Http\Controllers\Password\PasswordController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\User\UserController;

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

Route::post('transaction/pagseguro', [PaymentController::class, 'notify']);
Route::get('transaction/pagseguro/notify', [PaymentController::class, 'notifyEmail']);

Route::post('forgot-password', [UserController::class, 'forgotPassword']);
Route::post('reset-password', [UserController::class, 'resetPassword']);
Route::post('verify-registration', [UserController::class, 'verifyRegistration']);
Route::post('finish-registration', [UserController::class, 'finishRegistration']);

Route::group([ 'middleware' => 'api', 'prefix' => 'auth' ], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('get-passwords-from-extension', [AuthController::class, 'getPasswordsFromExtension']);
});

Route::middleware('auth')->group(function($router) {
    Route::group(['prefix' => 'auth'], function($router) {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('checkMasterPassword', [AuthController::class, 'checkMasterPassword']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::group(['prefix' => 'folders'], function($router) {
        Route::get('/', [FolderController::class, 'list']);
        Route::post('/', [FolderController::class, 'create']);
        Route::put('/{id}', [FolderController::class, 'update']);
        Route::delete('/{id}', [FolderController::class, 'delete']);
    });

    Route::group(['prefix' => 'passwords'], function($router) {
        Route::get('/', [PasswordController::class, 'list']);
        Route::post('/', [PasswordController::class, 'create']);
        Route::post('/import', [PasswordController::class, 'createMany']);
        Route::put('/{id}', [PasswordController::class, 'update']);
        Route::put('/{id}/folder/{folderId}', [PasswordController::class, 'updateFolder']);
        Route::put('/{id}/removeFolder', [PasswordController::class, 'removeFolder']);
        Route::delete('/{id}', [PasswordController::class, 'delete']);
    });

    Route::group(['prefix' => 'notes'], function($router) {
        Route::get('/', [NoteController::class, 'list']);
        Route::post('/', [NoteController::class, 'create']);
        Route::put('/{id}', [NoteController::class, 'update']);
        Route::delete('/{id}', [NoteController::class, 'delete']);
    });

    Route::group(['prefix' => 'user'], function($router) {
        Route::get('/list', [UserController::class, 'list']);
        Route::post('/', [UserController::class, 'create']);
        Route::get('/', [UserController::class, 'show']);
        // Route::put('/', [UserController::class, 'update']);
        Route::put('/email', [UserController::class, 'updateEmail']);
        Route::put('/password', [UserController::class, 'updatePassword']);
        Route::put('/masterPassword', [UserController::class, 'updateMasterPassword']);
        Route::delete('/{id}', [UserController::class, 'delete']);
    });
});
