<?php

use App\Http\Controllers\client\IndexController;
use Illuminate\Support\Facades\Route;

Route::prefix('client')->name('client.')->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('index');
});
