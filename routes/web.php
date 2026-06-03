<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
});

Route::get('/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/login', [AuthController::class, 'loginPost'])->name('admin.login.post');


Route::prefix('admin')->group(function () {
    // dashboard routes
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');

    // user routes
    Route::get('/users', [UserController::class, 'index'])->name('admin.user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.user.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.user.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.user.update');
    Route::get('/users/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

    // category routes
    Route::get('/category', [CategoryController::class, 'index'])->name('admin.category.index');
    Route::get('/category/create', [CategoryController::class, 'create'])->name('admin.category.create');
    Route::post('/category/store', [CategoryController::class, 'store'])->name('admin.category.store');
    Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('admin.category.edit');
    Route::put('/category/update', [CategoryController::class, 'update'])->name('admin.category.update');
    Route::delete('/category/{id}', [CategoryController::class, 'destroy'])->name('admin.category.destroy');
});
