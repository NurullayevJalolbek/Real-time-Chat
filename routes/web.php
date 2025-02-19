<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SidebarController;

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        return view('app');
    })->name('app');

    Route::get('/sidebar-chats', [SidebarController::class, 'index'])->name('sidebar-chats');
    Route::get('/chat/{id}', [MessageController::class, 'show'])->name('chat');
    
    Route::post('/send-message', [MessageController::class, 'store'])->name('send.message');
    Route::post('/send-media', [MessageController::class, 'media'])->name('send.media');

});

require __DIR__.'/auth.php';

