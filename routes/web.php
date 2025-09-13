<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::middleware(['auth:web'])->group(function () {
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
Route::resource('product', App\Http\Controllers\ProductController::class);
Route::post('fetch-product', [App\Http\Controllers\ProductController::class, 'fetchProducts'])->name('fetch-product');
Route::post('create-am-products', [App\Http\Controllers\ProductController::class, 'createAmProducts'])->name('create-am-products');
Route::resource('setting', App\Http\Controllers\SettingController::class);
Route::resource('account', App\Http\Controllers\AccountController::class);
Route::post('update-password', [App\Http\Controllers\AccountController::class,'update_password'])->name('update-password');
Route::resource('order', App\Http\Controllers\OrderController::class);
Route::post('fetch-order', [App\Http\Controllers\OrderController::class, 'fetchOrders'])->name('fetch-order');
Route::post('create-am-orders', [App\Http\Controllers\OrderController::class, 'createAmOrders'])->name('create-am-orders');
Route::post('/orders/fulfil', [App\Http\Controllers\OrderController::class, 'fulfilfulOrder'])->name('order.fulfil');

});