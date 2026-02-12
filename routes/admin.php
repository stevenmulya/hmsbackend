<?php

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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:admin', 'abilities:admin'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/files/download', [FileController::class, 'download']);

    Route::get('/customers/export-csv', [CustomerController::class, 'exportCsv']);
    Route::apiResource('customers', CustomerController::class)->parameters([
        'customers' => 'customer:customer_username'
    ]);
    
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('sub-categories', SubCategoryController::class);
    
    Route::patch('/products/{id}/toggle-price', [ProductController::class, 'toggleSinglePrice']);
    Route::get('/products/export-csv', [ProductController::class, 'exportCsv']);
    Route::patch('/products/toggle-all-prices', [ProductController::class, 'toggleAllPrices']);
    Route::apiResource('products', ProductController::class)->parameters([
        'products' => 'product:slug'
    ]);

    Route::get('/quotations/export-csv/list', [QuotationController::class, 'exportCsvList']);
    Route::get('/quotations/export-csv/detail/{quotation}', [QuotationController::class, 'exportCsvDetail']);
    Route::get('/quotations/{customer:customer_username}/{quotation}', [QuotationController::class, 'showByCustomer']);
    Route::apiResource('quotations', QuotationController::class)->except(['show']);

    Route::apiResource('blog-categories', BlogCategoryController::class);
    Route::apiResource('blogs', BlogController::class)->parameters([
        'blogs' => 'blog:slug'
    ]);

    Route::patch('/faqs/update-order', [FaqController::class, 'updateOrder']);
    Route::apiResource('faqs', FaqController::class);

    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('socials', SocialController::class);
});
