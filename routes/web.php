<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Landing/Index'))->name('landing');
Route::get('/about', fn () => Inertia::render('About/Index'))->name('about');

Route::get('/articles', fn () => Inertia::render('Articles/Index'))->name('articles.index');
Route::get('/articles/{slug}', fn (string $slug) => Inertia::render('Articles/Show', ['slug' => $slug]))->name('articles.show');

Route::get('/store', fn () => Inertia::render('Store/Index'))->name('store.index');
Route::get('/store/{slug}', fn (string $slug) => Inertia::render('Store/Show', ['slug' => $slug]))->name('store.show');

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminPageController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    Route::get('/comments', [AdminPageController::class, 'comments'])->name('comments');
    Route::get('/{resourceKey}', [AdminPageController::class, 'resource'])
        ->whereIn('resourceKey', [
            'products', 'product-categories', 'services', 'service-categories',
            'clients', 'articles', 'article-categories',
            'team-members', 'event-categories', 'events',
            'users',
        ])
        ->name('resource');
});
