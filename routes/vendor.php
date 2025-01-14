<?php

// Vendor Routes
use App\Http\Controllers\Vendor\OrderController;
use App\Http\Controllers\Vendor\ReturnController;
use App\Http\Controllers\Vendor\RefundController;
use App\Http\Controllers\Vendor\CouponController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\PromotionController;
use App\Http\Controllers\Vendor\EmailCampaignController;

Route::group(['middleware' => ['vendor'], 'prefix' => 'vendor'], function () {
    // Dashboard
    Route::get('dashboard', DashboardController::class); // Vendor dashboard

    // Product Management
    Route::resource('products', ProductController::class)->except('show');

    // Order Management
    Route::get('orders', [OrderController::class, 'index']); // List vendor's orders
    Route::get('orders/{order}', [OrderController::class, 'show']); // View order details

    // Returns and Refunds
    Route::get('returns', [ReturnController::class, 'index']); // List vendor's returns
    Route::get('refunds', [RefundController::class, 'index']); // List vendor's refunds

    // Promotions and Marketing
    Route::resource('coupons', CouponController::class)->except('show');
    Route::resource('promotions', PromotionController::class)->except('show');
    Route::resource('email-campaigns', EmailCampaignController::class)->except('show');
});
