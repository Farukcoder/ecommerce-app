<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('products', 'product.products')->middleware(['auth'])->name('products');
