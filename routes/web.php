<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatusController;
use App\Livewire\Counter;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/counter', Counter::class)->name('counter');

// API routes
Route::prefix('api')->group(function () {
    Route::get('/status', StatusController::class)->name('api.status');
});