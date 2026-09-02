<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Landing/Index'))->name('landing');

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
