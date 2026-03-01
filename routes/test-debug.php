<?php
use Illuminate\Support\Facades\Route;

Route::get('/debug-test', function() {
    return response()->json(['test' => 'works']);
});
