<?php
// --- Routes for the Customer Frontend (hmsfrontend) ---
// This file defines all public and protected endpoints for customers.
// (Pastikan ini adalah isi dari file routes/api.php Anda jika ini file utama Anda)

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
use App\Http\Controllers\Api\Customer\BlogCategoryController; // Pastikan ini juga ada

/*
|--------------------------------------------------------------------------
| Customer API Routes
|--------------------------------------------------------------------------
*/

// --- Public Routes (do not require login) ---

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-verification-otp', [AuthController::class, 'resendVerificationOtp']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

// Public Product Catalog
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);

// Public Blog
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{blog:slug}', [BlogController::class, 'show']);

// Public Partners
Route::get('/partners', [PartnerController::class, 'index']);

// Public Social Links/Contacts
Route::get('/socials', [SocialController::class, 'index']);

// Public Product Categories
Route::get('/categories', [CategoryController::class, 'index']);

// Public Blog Categories
Route::get('/blog-categories', [BlogCategoryController::class, 'index']);


// --- Protected Routes (require a valid login token) ---
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    // Quotation (Cart) Routes
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::get('/my-quotations', [QuotationController::class, 'index']);
});