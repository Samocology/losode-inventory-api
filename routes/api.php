<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Order routes
Route::post('/orders', [OrderController::class, 'place']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Vendor product management
    Route::get('/vendor/products', [ProductController::class, 'vendorProducts']);
    Route::post('/vendor/products', [ProductController::class, 'store']);
    Route::put('/vendor/products/{product}', [ProductController::class, 'update']);
    Route::delete('/vendor/products/{product}', [ProductController::class, 'destroy']);

    // Order history
    Route::get('/orders/history', [OrderController::class, 'history']);
});