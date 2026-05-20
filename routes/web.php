<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HeaderSettingController;
use App\Http\Controllers\SystemSettingController;
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
    Route::get('/products/import',             [ProductController::class, 'importForm'])->name('import.form');
    Route::get('/products/import/sample',      [ProductController::class, 'downloadImportSample'])->name('import.sample');
    Route::post('/products/import',            [ProductController::class, 'importStore'])->name('import.store');
    Route::post('/products',                   [ProductController::class, 'store'])->name('store');
    Route::get('/products/{product}',          [ProductController::class, 'show'])->name('show');
    Route::get('/products/{product}/edit',     [ProductController::class, 'edit'])->name('edit');
    Route::put('/products/{product}',          [ProductController::class, 'update'])->name('update');
    Route::delete('/products/{product}',       [ProductController::class, 'destroy'])->name('destroy');
});

// Order management routes
Route::middleware(['auth'])->prefix('dashboard')->name('orders.')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    Route::post('/orders/{order}/refund', [OrderController::class, 'refund'])->name('refund');
    Route::patch('/orders/{order}/note', [OrderController::class, 'updateAdminNote'])->name('note');
    Route::post('/orders/bulk-status', [OrderController::class, 'bulkUpdateStatus'])->name('bulk-status');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice');
});

// Attribute CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('attributes.')->group(function () {
    Route::get('/attributes',                      [AttributeController::class, 'index'])->name('index');
    Route::get('/attributes/create',               [AttributeController::class, 'create'])->name('create');
    Route::post('/attributes',                     [AttributeController::class, 'store'])->name('store');
    Route::get('/attributes/{attribute}/edit',     [AttributeController::class, 'edit'])->name('edit');
    Route::put('/attributes/{attribute}',          [AttributeController::class, 'update'])->name('update');
    Route::delete('/attributes/{attribute}',       [AttributeController::class, 'destroy'])->name('destroy');
});

// Brand CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('brands.')->group(function () {
    Route::get('/brands',                  [BrandController::class, 'index'])->name('index');
    Route::get('/brands/create',           [BrandController::class, 'create'])->name('create');
    Route::post('/brands',                 [BrandController::class, 'store'])->name('store');
    Route::get('/brands/{brand}/edit',     [BrandController::class, 'edit'])->name('edit');
    Route::put('/brands/{brand}',          [BrandController::class, 'update'])->name('update');
    Route::delete('/brands/{brand}',       [BrandController::class, 'destroy'])->name('destroy');
});

// Category CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('categories.')->group(function () {
    Route::get('/categories',                      [CategoryController::class, 'index'])->name('index');
    Route::get('/categories/create',               [CategoryController::class, 'create'])->name('create');
    Route::post('/categories',                     [CategoryController::class, 'store'])->name('store');
    Route::get('/categories/{category}/edit',      [CategoryController::class, 'edit'])->name('edit');
    Route::put('/categories/{category}',           [CategoryController::class, 'update'])->name('update');
    Route::delete('/categories/{category}',        [CategoryController::class, 'destroy'])->name('destroy');
});

// System Setting routes
Route::middleware(['auth'])->prefix('dashboard')->name('system-settings.')->group(function () {
    Route::get('/system-settings',                 [SystemSettingController::class, 'index'])->name('index');
    Route::get('/system-settings/create',          [SystemSettingController::class, 'create'])->name('create');
    Route::post('/system-settings',                [SystemSettingController::class, 'store'])->name('store');
    Route::get('/system-settings/{systemSetting}/edit', [SystemSettingController::class, 'edit'])->name('edit');
    Route::put('/system-settings/{systemSetting}',  [SystemSettingController::class, 'update'])->name('update');
});

// Header Setting routes
Route::middleware(['auth'])->prefix('dashboard')->name('header-settings.')->group(function () {
    Route::get('/header-settings',                 [HeaderSettingController::class, 'index'])->name('index');
    Route::get('/header-settings/create',          [HeaderSettingController::class, 'create'])->name('create');
    Route::post('/header-settings',                [HeaderSettingController::class, 'store'])->name('store');
    Route::get('/header-settings/{headerSetting}/edit', [HeaderSettingController::class, 'edit'])->name('edit');
    Route::put('/header-settings/{headerSetting}',  [HeaderSettingController::class, 'update'])->name('update');
});
