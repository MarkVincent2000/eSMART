<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;

// User Management routes
Route::prefix('user-management')->group(function () {
    Route::get('index', [UserController::class, 'index'])->name('user-management.index');
    // JSON list for front-end table
    Route::get('users', [UserController::class, 'list'])->name('user-management.users');

    // Create user (form submit from modal)
    Route::post('users', [UserController::class, 'store'])->name('user-management.users.store');
});


