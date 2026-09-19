<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TokenManagerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/token/create', [TokenManagerController::class, 'create']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('v1')->group(function () {
        Route::apiResource('categories', CategoryController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy']);

        Route::apiResource('products', ProductController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy']);
    });
});
