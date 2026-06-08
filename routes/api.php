<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\SystemSettingController;
use App\Http\Controllers\Api\Customer\ReviewController as CustomerReviewController;
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
    Route::get('/products/{product}/reviews', [ProductController::class, 'reviews'])->name('products.reviews');
    Route::get('/system-settings', [SystemSettingController::class, 'show'])->name('system-settings.show');
    Route::get('/support-tickets/subjects', [SupportTicketController::class, 'subjects'])->name('support-tickets.subjects');
    Route::post('/support-tickets', [SupportTicketController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('support-tickets.store');
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

        Route::get('/checkout/options', [CustomerOrderController::class, 'options'])->name('checkout.options');
        Route::post('/checkout/quote', [CustomerOrderController::class, 'quote'])->name('checkout.quote');

        Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/track/{orderIdentifier}', [CustomerOrderController::class, 'track'])->name('orders.track');
        Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');

        // Review routes
        Route::get('/reviews', [CustomerReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/pending', [CustomerReviewController::class, 'pending'])->name('reviews.pending');
        Route::post('/reviews', [CustomerReviewController::class, 'store'])->name('reviews.store');
    });
});

Route::prefix('admin')
    ->middleware(['auth:sanctum'])
    ->name('admin.')
    ->group(function () {
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/refund', [AdminOrderController::class, 'refund'])->name('orders.refund');
        Route::patch('/orders/{order}/note', [AdminOrderController::class, 'updateAdminNote'])->name('orders.note');
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

        Route::patch('/refunds/{refund}', [AdminRefundController::class, 'update'])->name('refunds.update');
    });
