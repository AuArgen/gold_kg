<?php

use App\Http\Controllers\public\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index'])->name('public.index');

Route::get('createAdmin',[IndexController::class, 'createAdmin'])->name('public.createAdmin');
