<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('dashboard/products', 'dashboard.products')->middleware(['auth'])->name('dashboard.products');

// Product CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('products.')->group(function () {
    Route::get('/products',         [ProductController::class, 'index'])->name('index');
    Route::get('/products/create',  [ProductController::class, 'create'])->name('create');
    Route::post('/products',        [ProductController::class, 'store'])->name('store');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('destroy');
});
