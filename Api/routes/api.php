<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// use App\Http\Controllers\Api\BannerController;
// use App\Http\Controllers\Api\ProductController;
// use App\Http\Controllers\Api\BlogController;
// use App\Http\Controllers\Api\OfferController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


    // Example: get current authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
});

// Public routes
// Route::get('/banners', [BannerController::class, 'index']);
// Route::get('/featured-products', [ProductController::class, 'featured']);
// Route::get('/products', [ProductController::class, 'index']);
// Route::get('/blogs', [BlogController::class, 'index']);
// Route::get('/offers', [OfferController::class, 'index']);

