<?php
use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'guest'], function () {
    // Login page
    Route::get('/login', function() { return view('auth.login'); })->name('login');
    Route::post('/login', [AuthUserController::class, 'login'])->name('login.post');

    // Register page
    Route::get('/register', function() { return view('auth.register'); })->name('register');
    Route::post('/register', [AuthUserController::class, 'register'])->name('register.post');

    // Forgot password page
    Route::get('/forgot-password', function() { return view('auth.forgot-password'); })->name('forgot-password');
    Route::post('/forgot-password', [AuthUserController::class, 'forgotPassword'])->name('forgot-password.post');
});
