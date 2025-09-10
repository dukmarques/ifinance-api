<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardExpensesController;
use App\Http\Controllers\CardInstallmentsController;
use App\Http\Controllers\CardsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ExpenseAssigneesController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\RevenuesController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return ['message' => 'pong'];
});

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
    Route::apiResource('categories', CategoriesController::class);
    Route::apiResource('revenues', RevenuesController::class);
    Route::post('/expenses/{id}/update-expense-payment-status', [ExpensesController::class, 'updateExpensePaymentStatus']);
    Route::apiResource('expenses', ExpensesController::class);
    Route::apiResource('card-expenses', CardExpensesController::class);
    Route::apiResource('card-expenses.installments', CardInstallmentsController::class);
    Route::apiResource('expense-assignees', ExpenseAssigneesController::class);
});
