<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NewsClippingController;
use App\Http\Middleware\ApiTokenMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(ApiTokenMiddleware::class)->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/contracts', [ContractController::class, 'index']);
    Route::get('/clippings', [NewsClippingController::class, 'index']);
    Route::post('/clippings', [NewsClippingController::class, 'store']);
    Route::get('/invoices', [InvoiceController::class, 'index']);
});
