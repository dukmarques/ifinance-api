<?php

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

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::apiResource('users', UsersController::class);
Route::apiResource('users.cards', CardsController::class);
Route::apiResource('users.categories', CategoryController::class);
Route::apiResource('users.transactions', TransactionsController::class);
