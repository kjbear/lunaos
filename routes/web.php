<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/**
 * Dave Test: Hello route
 * 
 * Returns a simple greeting message for testing purposes.
 * 
 * @return \Illuminate\Http\JsonResponse
 */
Route::get('/hello-dave-test', function () {
    return response()->json([
        'message' => 'Hello from Dave!'
    ]);
});
