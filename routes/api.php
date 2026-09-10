<?php

use App\Http\Controllers\Api\AdminArticleCommentController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminMediaController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\ArticleCategoryController;
use App\Http\Controllers\Api\ArticleCommentController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\EventCategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TeamMemberController;
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
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/cart', [CartController::class, 'show']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::post('/cart/items', [CartItemController::class, 'store']);
    Route::patch('/cart/items/{cartItem}', [CartItemController::class, 'update']);
    Route::delete('/cart/items/{cartItem}', [CartItemController::class, 'destroy']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/article-comments', [AdminArticleCommentController::class, 'index']);
    Route::post('/media', [AdminMediaController::class, 'store']);
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::patch('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
});

// Clients
Route::get('/clients', [ClientController::class, 'index'])->middleware('throttle:60,1');
Route::get('/clients/{slug}', [ClientController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/clients', [ClientController::class, 'store']);
    Route::patch('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);
});

// Services
Route::get('/services', [ServiceController::class, 'index'])->middleware('throttle:60,1');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/services', [ServiceController::class, 'store']);
    Route::patch('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
});

// Service Categories
Route::get('/service-categories', [ServiceCategoryController::class, 'index'])->middleware('throttle:60,1');
Route::get('/service-categories/{slug}/services', [ServiceCategoryController::class, 'services'])->middleware('throttle:60,1');
Route::get('/service-categories/{slug}', [ServiceCategoryController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/service-categories', [ServiceCategoryController::class, 'store']);
    Route::patch('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'update']);
    Route::delete('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'destroy']);
});

// Product Categories
Route::get('/product-categories', [ProductCategoryController::class, 'index'])->middleware('throttle:60,1');
Route::get('/product-categories/{slug}', [ProductCategoryController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/product-categories', [ProductCategoryController::class, 'store']);
    Route::patch('/product-categories/{productCategory}', [ProductCategoryController::class, 'update']);
    Route::delete('/product-categories/{productCategory}', [ProductCategoryController::class, 'destroy']);
});

// Product
Route::get('/products', [ProductController::class, 'index'])->middleware('throttle:60,1');
Route::get('/products/{slug}', [ProductController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});

// Article Categories
Route::get('/article-categories', [ArticleCategoryController::class, 'index'])->middleware('throttle:60,1');
Route::get('/article-categories/{slug}/articles', [ArticleCategoryController::class, 'articles'])->middleware('throttle:60,1');
Route::get('/article-categories/{slug}', [ArticleCategoryController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/article-categories', [ArticleCategoryController::class, 'store']);
    Route::patch('/article-categories/{articleCategory}', [ArticleCategoryController::class, 'update']);
    Route::delete('/article-categories/{articleCategory}', [ArticleCategoryController::class, 'destroy']);
});

// Articles
Route::get('/articles', [ArticleController::class, 'index'])->middleware('throttle:60,1');
Route::get('/articles/{article:slug}/comments', [ArticleCommentController::class, 'index'])->middleware('throttle:60,1');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::patch('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
});

// Article Comments
Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('/articles/{article:slug}/comments', [ArticleCommentController::class, 'store']);
    Route::patch('/article-comments/{articleComment}', [ArticleCommentController::class, 'update']);
    Route::delete('/article-comments/{articleComment}', [ArticleCommentController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::patch('/article-comments/{articleComment}/moderate', [ArticleCommentController::class, 'moderate']);
});

// Team Members
Route::get('/team-members', [TeamMemberController::class, 'index'])->middleware('throttle:60,1');
Route::get('/team-members/{teamMember}', [TeamMemberController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/team-members', [TeamMemberController::class, 'store']);
    Route::patch('/team-members/{teamMember}', [TeamMemberController::class, 'update']);
    Route::delete('/team-members/{teamMember}', [TeamMemberController::class, 'destroy']);
});

// Event Categories
Route::get('/event-categories', [EventCategoryController::class, 'index'])->middleware('throttle:60,1');
Route::get('/event-categories/{slug}', [EventCategoryController::class, 'show'])->middleware('throttle:60,1');
Route::get('/event-categories/{slug}/events', [EventCategoryController::class, 'events'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/event-categories', [EventCategoryController::class, 'store']);
    Route::patch('/event-categories/{eventCategory}', [EventCategoryController::class, 'update']);
    Route::delete('/event-categories/{eventCategory}', [EventCategoryController::class, 'destroy']);
});

// Events
Route::get('/events', [EventController::class, 'index'])->middleware('throttle:60,1');
Route::get('/events/{slug}', [EventController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'admin', 'throttle:30,1'])->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::patch('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
});
