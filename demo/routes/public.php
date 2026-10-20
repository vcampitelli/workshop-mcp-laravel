<?php

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckToken;
use App\Http\Controllers\Public\OrderController;
use App\Http\Controllers\Public\ReviewController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\ProductCategoryController;

Route::middleware('auth:api')->group(function () {
    Route::get('products', [ProductController::class, 'index'])->middleware(CheckToken::using('products:list'));
    Route::get('products/{product}', [ProductController::class, 'show'])->middleware(CheckToken::using('products:show'));

    Route::get('product-categories', [ProductCategoryController::class, 'index'])->middleware(CheckToken::using('product-categories:list'));
    Route::get('product-categories/{category}', [ProductCategoryController::class, 'show'])->middleware(CheckToken::using('product-categories:show'));

    Route::get('products/{product}/reviews', [ReviewController::class, 'index'])->middleware(CheckToken::using('reviews:list'));
});

Route::get('orders', [OrderController::class, 'index'])->middleware(CheckToken::using('orders:list'));
Route::get('orders/create', [OrderController::class, 'create'])->middleware(CheckToken::using('orders:create'));
Route::post('orders', [OrderController::class, 'store'])->middleware(CheckToken::using('orders:create'));
Route::get('orders/{order}', [OrderController::class, 'show'])->middleware(CheckToken::using('orders:show'));
Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->middleware(CheckToken::using('orders:update'));
Route::match(['put', 'patch'], 'orders/{order}', [OrderController::class, 'update'])->middleware(CheckToken::using('orders:update'));
Route::delete('orders/{order}', [OrderController::class, 'destroy'])->middleware(CheckToken::using('orders:delete'));
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware(CheckToken::using('orders:update-status'));
