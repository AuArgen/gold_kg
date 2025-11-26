<?php

use App\Http\Controllers\public\ContactController;
use App\Http\Controllers\public\IndexController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'index'])->name('public.index');
Route::get('/contact', [IndexController::class, 'contact'])->name('public.contact');
Route::post('/submit_contact', [ContactController::class, 'submit'])->name('public.submit_contact');
Route::get('/countUser', [IndexController::class, 'countUser'])->name('public.countUser');

Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/latest', [ProductController::class, 'getLatest'])->name('products.latest');
Route::get('/products/brands', [ProductController::class, 'getBrands'])->name('products.brands');
