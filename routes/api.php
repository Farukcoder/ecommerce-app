<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('home')->name('home.')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
});

Route::prefix('customer')->name('customer.')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('register');

    Route::post('/login', [CustomerAuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [CustomerAuthController::class, 'me'])->name('me');
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });
});
