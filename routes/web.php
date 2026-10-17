<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartPageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Users\UserController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('products.index'))->name('home');

Route::resource('products', ProductController::class)->only(['index', 'show']);
Route::middleware([IsAdmin::class])->group(function () {
    Route::resource('products', ProductController::class)->except(['index', 'show']);
});
Route::resource('users', UserController::class)->middleware(IsAdmin::class);

Route::post('/register', Register::class)->name('register');
Route::post('/login', [Login::class, 'login'])->name('login');
Route::get('/logout', [Login::class, 'logout'])->name('logout');

Route::get('/cart/getById', [CartController::class, 'getById'])->name('cart.getById');
Route::post('/cart/sync', [CartController::class, 'sync'])->name('cart.sync');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/merge', [CartController::class, 'merge'])->name('cart.merge');
Route::post('/cart/guest-details', [CartController::class, 'guestDetails'])
    ->name('cart.guest.details');

Route::get('/cart', [CartPageController::class, 'index'])->name('cart.index');

Route::get('admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard')
    ->middleware(IsAdmin::class);

Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'myOrders'])->name('orders.my');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});

// Все заказы — только для админа
Route::middleware(IsAdmin::class)->group(function () {
    Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [OrderController::class, 'adminShow'])->name('admin.orders.show');
});
