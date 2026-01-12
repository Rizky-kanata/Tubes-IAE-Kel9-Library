<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes - Library Management System
|--------------------------------------------------------------------------
| Base URL: http://127.0.0.1:8000/api
| All routes automatically prefixed with /api
|--------------------------------------------------------------------------
*/

// ============================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================

// Authentication
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Books - Public Access (Anyone can view)
Route::get('/books', [BookController::class, 'index'])->name('api.books.index');
Route::get('/books/search', [BookController::class, 'search'])->name('api.books.search');
Route::get('/books/{id}', [BookController::class, 'show'])->name('api.books.show');

// ============================================
// PROTECTED ROUTES (Require JWT Token)
// ============================================
Route::middleware('auth:api')->group(function () {

    // ============================================
    // AUTH ENDPOINTS
    // ============================================
    Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');

    // ============================================
    // BOOKS CRUD (Admin Only)
    // ✅ Sesuai dokumentasi: /api/books (POST, PUT, DELETE)
    // ============================================
    Route::middleware('admin')->group(function () {
        Route::post('/books', [BookController::class, 'store'])->name('api.books.store');
        Route::put('/books/{id}', [BookController::class, 'update'])->name('api.books.update');
        Route::delete('/books/{id}', [BookController::class, 'destroy'])->name('api.books.destroy');
    });

    // ============================================
    // USER TRANSACTIONS (Member)
    // ============================================
    Route::prefix('transactions')->name('api.transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::post('/borrow', [TransactionController::class, 'borrow'])->name('borrow');
        Route::post('/{id}/return', [TransactionController::class, 'return'])->name('return');
        Route::get('/{id}/fine', [TransactionController::class, 'checkFine'])->name('fine');
    });

    // ============================================
    // ADMIN ROUTES (Admin Only)
    // ============================================
    Route::middleware('admin')->prefix('admin')->name('api.admin.')->group(function () {

        // Dashboard Statistics
        Route::get('/stats', [AdminController::class, 'getDashboardStats'])->name('stats');

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'getUsers'])->name('index');
            Route::get('/stats', [UserController::class, 'stats'])->name('stats');
            Route::get('/members', [UserController::class, 'members'])->name('members');
            Route::get('/search', [UserController::class, 'search'])->name('search');
            Route::post('/', [AdminController::class, 'createUser'])->name('store');
            Route::get('/{id}', [AdminController::class, 'getUser'])->name('show');
            Route::put('/{id}', [AdminController::class, 'updateUser'])->name('update');
            Route::delete('/{id}', [AdminController::class, 'deleteUser'])->name('destroy');
        });

        // Transaction Management (Admin View All)
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'adminIndex'])->name('index');
            Route::put('/{id}', [TransactionController::class, 'update'])->name('update');
            Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('destroy');
        });
    });

    // ============================================
    // ALTERNATIVE USER ROUTES (Without /admin prefix)
    // For backward compatibility or direct access
    // ============================================
    Route::middleware('admin')->prefix('users')->name('api.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/stats', [UserController::class, 'stats'])->name('stats');
        Route::get('/members', [UserController::class, 'members'])->name('members');
        Route::get('/search', [UserController::class, 'search'])->name('search');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });
});

// ============================================
// UTILITY ENDPOINTS
// ============================================

// Health Check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'status' => 'running',
        'message' => 'API is healthy',
        'timestamp' => now()->toISOString(),
        'laravel_version' => app()->version(),
        'php_version' => phpversion()
    ]);
})->name('api.health');

// API Documentation
Route::get('/docs', function () {
    return response()->json([
        'success' => true,
        'message' => 'Library Management System API v1.0',
        'base_url' => url('/api'),
        'authentication' => [
            'type' => 'JWT Bearer Token',
            'header' => 'Authorization: Bearer {your_jwt_token}',
            'get_token' => 'POST /api/login'
        ],
        'endpoints' => [
            'authentication' => [
                'POST /api/register' => [
                    'description' => 'Register new user',
                    'auth_required' => false,
                    'body' => [
                        'name' => 'string (required)',
                        'email' => 'string (required, unique)',
                        'password' => 'string (required, min:6)',
                        'phone' => 'string (optional)',
                        'address' => 'string (optional)'
                    ]
                ],
                'POST /api/login' => [
                    'description' => 'Login and get JWT token',
                    'auth_required' => false,
                    'body' => [
                        'email' => 'string (required)',
                        'password' => 'string (required)'
                    ]
                ],
                'GET /api/me' => [
                    'description' => 'Get authenticated user info',
                    'auth_required' => true
                ],
                'POST /api/logout' => [
                    'description' => 'Logout user',
                    'auth_required' => true
                ],
                'POST /api/refresh' => [
                    'description' => 'Refresh JWT token',
                    'auth_required' => true
                ]
            ],
            'books' => [
                'GET /api/books' => [
                    'description' => 'Get all books',
                    'auth_required' => false,
                    'query_params' => [
                        'page' => 'integer (optional)',
                        'per_page' => 'integer (optional, default: 15)'
                    ]
                ],
                'GET /api/books/search' => [
                    'description' => 'Search books by keyword',
                    'auth_required' => false,
                    'query_params' => [
                        'q' => 'string (required, search keyword)'
                    ]
                ],
                'GET /api/books/{id}' => [
                    'description' => 'Get single book details',
                    'auth_required' => false
                ],
                'POST /api/books' => [
                    'description' => 'Create new book (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin',
                    'body' => [
                        'title' => 'string (required)',
                        'author' => 'string (required)',
                        'isbn' => 'string (required, unique)',
                        'publisher' => 'string (required)',
                        'year' => 'integer (required)',
                        'total_stock' => 'integer (required)',
                        'available_stock' => 'integer (required)',
                        'description' => 'text (optional)'
                    ]
                ],
                'PUT /api/books/{id}' => [
                    'description' => 'Update book (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin',
                    'body' => 'Same as POST /api/books'
                ],
                'DELETE /api/books/{id}' => [
                    'description' => 'Delete book (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin'
                ]
            ],
            'transactions' => [
                'GET /api/transactions' => [
                    'description' => 'Get user\'s transactions',
                    'auth_required' => true
                ],
                'POST /api/transactions/borrow' => [
                    'description' => 'Borrow a book',
                    'auth_required' => true,
                    'body' => [
                        'book_id' => 'integer (required)',
                        'borrow_date' => 'date (optional, default: today)',
                        'due_date' => 'date (optional, default: +7 days)'
                    ]
                ],
                'POST /api/transactions/{id}/return' => [
                    'description' => 'Return a borrowed book',
                    'auth_required' => true,
                    'body' => [
                        'return_date' => 'date (optional, default: today)'
                    ]
                ],
                'GET /api/transactions/{id}/fine' => [
                    'description' => 'Check fine amount for overdue book',
                    'auth_required' => true
                ]
            ],
            'admin' => [
                'GET /api/admin/stats' => [
                    'description' => 'Get dashboard statistics (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin'
                ],
                'GET /api/admin/users' => [
                    'description' => 'Get all users (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin'
                ],
                'POST /api/admin/users' => [
                    'description' => 'Create new user (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin'
                ],
                'GET /api/admin/users/{id}' => [
                    'description' => 'Get user details (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin'
                ],
                'PUT /api/admin/users/{id}' => [
                    'description' => 'Update user (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin'
                ],
                'DELETE /api/admin/users/{id}' => [
                    'description' => 'Delete user (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin'
                ],
                'GET /api/admin/transactions' => [
                    'description' => 'Get all transactions (Admin only)',
                    'auth_required' => true,
                    'role' => 'admin'
                ]
            ]
        ],
        'response_format' => [
            'success' => [
                'success' => true,
                'message' => 'Success message',
                'data' => 'Resource data or array'
            ],
            'error' => [
                'success' => false,
                'message' => 'Error message',
                'errors' => 'Validation errors (optional)'
            ]
        ],
        'status_codes' => [
            200 => 'Success',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Validation Error',
            500 => 'Internal Server Error'
        ]
    ]);
})->name('api.docs');

// ============================================
// FALLBACK ROUTE (404 Handler)
// ============================================
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
        'error' => 'The requested API endpoint does not exist',
        'request_info' => [
            'method' => request()->method(),
            'path' => request()->path(),
            'url' => request()->fullUrl()
        ],
        'hint' => 'Please check the API documentation',
        'documentation' => url('/api/docs'),
        'available_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'support' => 'For support, contact the system administrator'
    ], 404);
});
