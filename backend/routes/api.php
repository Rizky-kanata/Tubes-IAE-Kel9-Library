<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\TransactionController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (need JWT token)
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Books
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{id}', [BookController::class, 'show']);

    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::post('/books', [BookController::class, 'store']);
        Route::put('/books/{id}', [BookController::class, 'update']);
        Route::delete('/books/{id}', [BookController::class, 'destroy']);
    });

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions/borrow', [TransactionController::class, 'borrow']);
    Route::post('/transactions/{id}/return', [TransactionController::class, 'return']);
    Route::get('/transactions/{id}/fine', [TransactionController::class, 'checkFine']);
});
