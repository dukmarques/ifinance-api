<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use \App\Http\Controllers\CardsController;
use \App\Http\Controllers\CategoryController;
use \App\Http\Controllers\TransactionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/unauthenticated', function () {
    return ['message' => 'unauthenticated user'];
})->name('login');

Route::post('/users', [UsersController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/profile', [UsersController::class, 'show']);
    Route::put('/users/profile', [UsersController::class, 'update']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::apiResource('cards', CardsController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('transactions', TransactionsController::class);
});
