<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/system/status', function () {
    return response()->json([
        'status' => 'success',
        'message' => '180DC Uniar API is running',
        'data' => [
            'app' => config('app.name'),
            'environment' => app()->environment(),
            'backend' => 'Laravel',
            'frontend' => 'React TypeScript',
            'query' => 'TanStack Query',
            'database' => config('database.default'),
            'timestamp' => now()->toISOString(),
        ],
    ]);
});

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

// Todos
Route::get('/todos', [TodoController::class, 'index']);

Route::middleware('auth:sanctum', 'admin')->group(function () {
    Route::post('/todos', [TodoController::class, 'store']);
    Route::patch('/todos/{todo}', [TodoController::class, 'update']);
    Route::delete('/todos/{todo}', [TodoController::class, 'destroy']);
});

//Clients
Route::get('/clients', [ClientController::class, 'index']);

Route::middleware('auth:sanctum', 'admin')->group(function () {
    Route::post('/clients', [ClientController::class, 'store']);
    Route::patch('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);
});

// Services
Route::get('/services', [ServiceController::class, 'index']);

Route::middleware('auth:sanctum', 'admin')->group(function () {
    Route::post('/services', [ServiceController::class, 'store']);
    Route::patch('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
});

// Product Categories
Route::get('/product-categories', [ProductCategoryController::class, 'index']);
Route::get('/product-categories/{slug}', [ProductCategoryController::class, 'show']);

Route::middleware('auth:sanctum', 'admin')->group(function () {
    Route::post('/product-categories', [ProductCategoryController::class, 'store']);
    Route::patch('/product-categories/{productCategory}', [ProductCategoryController::class, 'update']);
    Route::delete('/product-categories/{productCategory}', [ProductCategoryController::class, 'destroy']);
});

// Product
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum', 'admin')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
