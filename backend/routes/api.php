<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// Public routes (Authentication only)
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes (Require Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user.profile');

    // CRUD Resource for Books
    Route::apiResource('books', BookController::class);
});
