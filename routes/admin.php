<?php

use App\Http\Controllers\admin\IndexController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/gold', [IndexController::class, 'gold'])->name('gold');
    Route::post('/parse', [IndexController::class, 'parseGold'])->name('parse');
});

