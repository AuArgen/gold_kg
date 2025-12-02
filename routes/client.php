<?php

use App\Http\Controllers\client\IndexController;
use App\Http\Controllers\UserGoldBarController;
use Illuminate\Support\Facades\Route;

Route::prefix('client')->name('client.')->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('index');
});

// Маршруты для управления золотыми слитками пользователя
Route::middleware('auth')->group(function () {
    Route::get('/my-gold', [UserGoldBarController::class, 'index'])->name('my-gold.index');
    Route::post('/my-gold', [UserGoldBarController::class, 'store'])->name('my-gold.store');
});
