<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\Customer\PasswordResetController;
use App\Http\Controllers\Api\Customer\ProductController;
use App\Http\Controllers\Api\Customer\QuotationController;
use App\Http\Controllers\Api\Customer\BlogController;
use App\Http\Controllers\Api\Customer\PartnerController;
use App\Http\Controllers\Api\Customer\SocialController;
use App\Http\Controllers\Api\Customer\CategoryController;
use App\Http\Controllers\Api\Customer\BlogCategoryController;
use App\Http\Controllers\Api\Customer\FaqController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-verification-otp', [AuthController::class, 'resendVerificationOtp']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{blog:slug}', [BlogController::class, 'show']);
Route::get('/partners', [PartnerController::class, 'index']);
Route::get('/socials', [SocialController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/blog-categories', [BlogCategoryController::class, 'index']);
Route::get('/faqs', [FaqController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::get('/my-quotations', [QuotationController::class, 'index']);
});