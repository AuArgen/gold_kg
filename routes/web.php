<?php

use Illuminate\Support\Facades\Route;

Route::middleware('log')->group(function () {
    require __DIR__ . '/auth.php';

    require __DIR__ . '/public.php';

    Route::middleware('auth')->group(function () {
        require __DIR__ . '/client.php';

        Route::middleware('admin')->group(function () {
            require __DIR__ . '/admin.php';
        });
    });
});

