<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Counter;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/counter', Counter::class)->name('counter');