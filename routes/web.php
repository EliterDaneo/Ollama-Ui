<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('chat.index');
});

Route::get('/chat', [\App\Http\Controllers\ChatUiController::class, 'index'])->name('chat.index');
Route::post('/chat/message',  [\App\Http\Controllers\ChatUiController::class, 'store']);
Route::delete('/chat/session', [\App\Http\Controllers\ChatUiController::class, 'destroy']);
