<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Redirect old dashboard.products URL to the real product index
Route::redirect('dashboard/products', '/dashboard/products', 301)->name('dashboard.products');

// Product CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('products.')->group(function () {
    Route::get('/products',                    [ProductController::class, 'index'])->name('index');
    Route::get('/products/create',             [ProductController::class, 'create'])->name('create');
    Route::post('/products',                   [ProductController::class, 'store'])->name('store');
    Route::get('/products/{product}/edit',     [ProductController::class, 'edit'])->name('edit');
    Route::put('/products/{product}',          [ProductController::class, 'update'])->name('update');
    Route::delete('/products/{product}',       [ProductController::class, 'destroy'])->name('destroy');
});
