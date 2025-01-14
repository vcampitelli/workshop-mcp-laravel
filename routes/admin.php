<?php

// Admin Routes
use App\Http\Controllers\RefundController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\ReturnStatusController;
use App\Http\Controllers\RefundStatusController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductTagController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductStatusController;
use App\Http\Controllers\Admin\EmailCampaignController;
use App\Http\Controllers\Admin\ShipmentStatusController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\EmailCampaignStatusController;

Route::group(['middleware' => ['admin'], 'prefix' => 'admin'], function () {
    // User Management
    Route::resource('users', UserController::class)->except(['show']); // Admin user management

    // Role and Permission Management
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->except(['show']);

    // Vendor Management
    Route::resource('vendors', VendorController::class);

    // Product Management
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('product-categories', ProductCategoryController::class)->except('show');
    Route::resource('product-attributes', ProductAttributeController::class)->except('show');
    Route::resource('product-statuses', ProductStatusController::class)->except('show');
    Route::resource('product-tags', ProductTagController::class)->except('show');

    // Payment Methods
    Route::resource('payment-methods', PaymentMethodController::class)->except('show');

    // Order Management
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']); // Update order status
    Route::resource('orders', OrderController::class)->only(['index', 'show']); // List all orders
    Route::resource('order-statuses', OrderStatusController::class)->except('show');

    // Shipment
    Route::resource('shipments', ShipmentController::class);
    Route::resource('shipment-statuses', ShipmentStatusController::class)->except('show');

    // Returns and Refunds
    Route::resource('returns', ReturnController::class);
    Route::resource('return-statuses', ReturnStatusController::class)->except('show');
    Route::resource('refunds', RefundController::class);
    Route::resource('refund-statuses', RefundStatusController::class)->except('show');

    // Promotions and Marketing
    Route::resource('coupons', CouponController::class);
    Route::resource('promotions', PromotionController::class);
    Route::resource('email-campaigns', EmailCampaignController::class);
    Route::resource('email-campaigns-status', EmailCampaignStatusController::class)->except('show');

    // Reviews Moderation
    Route::resource('reviews', ReviewController::class)->except(['edit', 'update']);
});
