<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-info', [AuthController::class, 'getUserInfo']); 
}); 

// <?php

// use App\Http\Controllers\AuthController;
// use Illuminate\Support\Facades\Route;

// // Public routes
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/refresh', [AuthController::class, 'refresh']);

// // Protected routes
// Route::middleware(['auth:api'])->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/user-info', [AuthController::class, 'getUserInfo']); 
// });