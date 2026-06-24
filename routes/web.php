<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HeaderSettingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SystemSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard.products');
});

// Redirect old dashboard.products URL to the real product index
Route::redirect('dashboard/products', '/dashboard/products', 301)->name('dashboard.products');

// Product CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('products.')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('create');
    Route::get('/products/import', [ProductController::class, 'importForm'])->name('import.form');
    Route::get('/products/import/sample', [ProductController::class, 'downloadImportSample'])->name('import.sample');
    Route::get('/products/stock', [ProductStockController::class, 'index'])->name('stock');
    Route::post('/products/{product}/stock', [ProductStockController::class, 'updateStock'])->name('stock.update');
    Route::get('/products/{product:uuid}/stock/details', [ProductStockController::class, 'stockDetails'])->name('stock.details');
    Route::post('/products/stock/add', [ProductStockController::class, 'addStock'])->name('stock.add');
    Route::post('/products/stock/create', [ProductStockController::class, 'createStock'])->name('stock.create');
    Route::post('/products/import', [ProductController::class, 'importStore'])->name('import.store');
    Route::post('/products', [ProductController::class, 'store'])->name('store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('destroy');
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

// Customer management routes

Route::middleware(['auth'])->prefix('dashboard')->name('customers.')->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('show');
});

// Report routes
Route::middleware(['auth'])->prefix('dashboard/reports')->name('reports.')->group(function () {
    Route::get('/sales', [SalesReportController::class, 'index'])->name('sales');
    Route::get('/sales/pdf', [SalesReportController::class, 'download'])->name('sales.pdf');
});

// Attribute CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('attributes.')->group(function () {
    Route::get('/attributes', [AttributeController::class, 'index'])->name('index');
    Route::get('/attributes/create', [AttributeController::class, 'create'])->name('create');
    Route::post('/attributes', [AttributeController::class, 'store'])->name('store');
    Route::get('/attributes/{attribute}/edit', [AttributeController::class, 'edit'])->name('edit');
    Route::put('/attributes/{attribute}', [AttributeController::class, 'update'])->name('update');
    Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy'])->name('destroy');
});

// Brand CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('brands.')->group(function () {
    Route::get('/brands', [BrandController::class, 'index'])->name('index');
    Route::get('/brands/create', [BrandController::class, 'create'])->name('create');
    Route::post('/brands', [BrandController::class, 'store'])->name('store');
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('edit');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('destroy');
});

// Category CRUD routes
Route::middleware(['auth'])->prefix('dashboard')->name('categories.')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

// Contact us routes
Route::middleware(['auth'])->prefix('dashboard')->name('contact-us.')->group(function () {
    Route::get('/contact-us', [ContactUsController::class, 'index'])->name('index');
    Route::get('/contact-us/{contactUsMessage}', [ContactUsController::class, 'show'])->name('show');
    Route::patch('/contact-us/{contactUsMessage}/status', [ContactUsController::class, 'updateStatus'])->name('status');
    Route::patch('/contact-us/{contactUsMessage}/note', [ContactUsController::class, 'updateAdminNote'])->name('note');
});

// Support ticket routes
Route::middleware(['auth'])->prefix('dashboard')->name('support-tickets.')->group(function () {
    Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('index');
    Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('show');
    Route::patch('/support-tickets/{supportTicket}/status', [SupportTicketController::class, 'updateStatus'])->name('status');
    Route::patch('/support-tickets/{supportTicket}/note', [SupportTicketController::class, 'updateAdminNote'])->name('note');
});

// Review management routes
Route::middleware(['auth'])->prefix('dashboard')->name('reviews.')->group(function () {
    Route::get('/reviews', [ReviewController::class, 'index'])->name('index');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('destroy');
});

// System Setting routes
Route::middleware(['auth'])->prefix('dashboard')->name('system-settings.')->group(function () {
    Route::get('/system-settings', [SystemSettingController::class, 'index'])->name('index');
    Route::get('/system-settings/create', [SystemSettingController::class, 'create'])->name('create');
    Route::post('/system-settings', [SystemSettingController::class, 'store'])->name('store');
    Route::get('/system-settings/{systemSetting}/edit', [SystemSettingController::class, 'edit'])->name('edit');
    Route::put('/system-settings/{systemSetting}', [SystemSettingController::class, 'update'])->name('update');
});

// Header Setting routes
Route::middleware(['auth'])->prefix('dashboard')->name('header-settings.')->group(function () {
    Route::get('/header-settings', [HeaderSettingController::class, 'index'])->name('index');
    Route::get('/header-settings/create', [HeaderSettingController::class, 'create'])->name('create');
    Route::post('/header-settings', [HeaderSettingController::class, 'store'])->name('store');
    Route::get('/header-settings/{headerSetting}/edit', [HeaderSettingController::class, 'edit'])->name('edit');
    Route::put('/header-settings/{headerSetting}', [HeaderSettingController::class, 'update'])->name('update');
});

// Location routes
Route::middleware(['auth'])->prefix('dashboard')->name('locations.')->group(function () {
    Route::get('/locations', [LocationController::class, 'index'])->name('index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('create');
    Route::post('/locations', [LocationController::class, 'store'])->name('store');
    Route::get('/locations/{type}/{id}/edit', [LocationController::class, 'edit'])->name('edit');
    Route::put('/locations/{type}/{id}', [LocationController::class, 'update'])->name('update');
    Route::delete('/locations/{type}/{id}', [LocationController::class, 'destroy'])->name('destroy');
});
