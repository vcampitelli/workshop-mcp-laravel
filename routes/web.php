<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/public.php';
require __DIR__ . '/customer.php';
require __DIR__ . '/vendor.php';
require __DIR__ . '/admin.php';
