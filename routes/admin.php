<?php
// --- Routes for the Admin Panel (Final & Simplified Version) ---
// This file defines all API endpoints accessible by the hmsadmin application.
// Ordering routes for partners and socials have been removed for simplicity.

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\FileController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\SubCategoryController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\QuotationController;
use App\Http\Controllers\Api\Admin\BlogCategoryController;
use App\Http\Controllers\Api\Admin\BlogController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\PartnerController;
use App\Http\Controllers\Api\Admin\SocialController;

// --- Public Admin Route ---
Route::post('/login', [AuthController::class, 'login']);

// --- Protected Admin Routes ---
Route::middleware(['auth:admin', 'abilities:admin'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Secure File Download Route
    Route::get('/files/download', [FileController::class, 'download']);

    // Customer Management Routes
    Route::get('/customers/export-csv', [CustomerController::class, 'exportCsv']);
    Route::apiResource('customers', CustomerController::class)->parameters([
        'customers' => 'customer:customer_username'
    ]);
    
    // Catalog Management Routes
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('sub-categories', SubCategoryController::class);
    Route::get('/products/export-csv', [ProductController::class, 'exportCsv']);
    Route::apiResource('products', ProductController::class)->parameters([
        'products' => 'product:slug'
    ]);

    // Quotation Management Routes
    Route::get('/quotations/export-csv/list', [QuotationController::class, 'exportCsvList']);
    Route::get('/quotations/export-csv/detail/{quotation}', [QuotationController::class, 'exportCsvDetail']);
    Route::get('/quotations/{customer:customer_username}/{quotation}', [QuotationController::class, 'showByCustomer']);
    Route::apiResource('quotations', QuotationController::class)->except(['show']);

    // Blog Management Routes
    Route::apiResource('blog-categories', BlogCategoryController::class);
    Route::apiResource('blogs', BlogController::class)->parameters([
        'blogs' => 'blog:slug'
    ]);

    // FAQ Management Routes
    Route::patch('/faqs/update-order', [FaqController::class, 'updateOrder']);
    Route::apiResource('faqs', FaqController::class);

    // --- THE FIX IS HERE: Simplified Partner Management Routes ---
    // The patch route for ordering has been removed.
    Route::apiResource('partners', PartnerController::class);

    // --- THE FIX IS HERE: Simplified Social Link Management Routes ---
    // The patch route for ordering has been removed.
    Route::apiResource('socials', SocialController::class);
});