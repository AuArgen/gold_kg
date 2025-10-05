<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
   Route::get('login', [AuthController::class, 'login'] )->name('login');
    Route::get('logout', [AuthController::class, 'logout'] )->name('logout');
    Route::get('register', [AuthController::class, 'register'] )->name('register');


    Route::get('/google', [AuthController::class, 'google'])->name('google.login');

    Route::get('/google/callback', [AuthController::class, 'googleCallback'])->name('google.callback');
});

