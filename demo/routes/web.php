<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthDemoController;
use Illuminate\Support\Facades\Route;

if (app()->environment(['local', 'testing'])) {
    Route::get('/demo/oauth/start', [OAuthDemoController::class, 'start'])
        ->middleware('signed')->name('oauth.demo.start');
    Route::get('/demo/oauth/callback', [OAuthDemoController::class, 'callback'])
        ->name('oauth.demo.callback');
}

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
