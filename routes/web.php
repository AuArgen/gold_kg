<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

require __DIR__ . '/public.php';

Route::middleware('auth')->group(function () {
    require __DIR__ . '/client.php';

    require __DIR__ . '/admin.php';
});
