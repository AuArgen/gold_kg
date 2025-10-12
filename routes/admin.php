<?php

use App\Http\Controllers\admin\IndexController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/gold', [IndexController::class, 'gold'])->name('gold');
    Route::get('/logs', [IndexController::class, 'logs'])->name('logs');
    Route::get('/contact', [IndexController::class, 'contact'])->name('contact');
    Route::post('/parse', [IndexController::class, 'parseGold'])->name('parse');
});

