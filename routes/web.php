<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Users\UserController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::resource('products', ProductController::class)->only(['index', 'show']);
Route::middleware([IsAdmin::class])->group(function () {
    Route::resource('products', ProductController::class)->except(['index', 'show']);
});
Route::resource('users', UserController::class)->middleware(IsAdmin::class);

Route::post('/register', Register::class)->name('register');
Route::post('/login', [Login::class, 'login'])->name('login');
Route::get('/logout', [Login::class, 'logout'])->name('logout');

Route::get('admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard')
    ->middleware(IsAdmin::class);
