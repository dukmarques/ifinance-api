<?php

use App\Http\Controllers\CardsController;
use App\Http\Controllers\RevenuesController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ExpensesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('revenues', RevenuesController::class);
    Route::apiResource('cards', CardsController::class);
    Route::apiResource('categories', CategoriesController::class);
    Route::apiResource('expenses', ExpensesController::class);
});
