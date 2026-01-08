<?php

namespace App\GraphQL\Mutations;

use App\Models\Book;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BorrowBook
{
    public function __invoke($root, array $args)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'data' => null,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'details' => 'Please login first'
                    ]
                ];
            }

            $book = Book::find($args['book_id']);

            if (!$book) {
                return [
                    'success' => false,
                    'message' => 'Book not found',
                    'data' => null,
                    'error' => [
                        'code' => 'BOOK_NOT_FOUND',
                        'details' => 'Book ID tidak ditemukan'
                    ]
                ];
            }

            if ($book->available_stock <= 0) {
                return [
                    'success' => false,
                    'message' => 'Book is not available',
                    'data' => null,
                    'error' => [
                        'code' => 'BOOK_UNAVAILABLE',
                        'details' => 'Stock habis'
                    ]
                ];
            }

            $activeBorrow = Transaction::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->whereNull('return_date')
                ->first();

            if ($activeBorrow) {
                return [
                    'success' => false,
                    'message' => 'You already borrowed this book',
                    'data' => null,
                    'error' => [
                        'code' => 'ALREADY_BORROWED',
                        'details' => 'User sudah pinjam buku ini'
                    ]
                ];
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrow_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays(14),
                'status' => 'borrowed'
            ]);

            $book->decrement('available_stock');

            return [
                'success' => true,
                'message' => 'Book borrowed successfully',
                'data' => $transaction->load(['book', 'user']),
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to borrow book',
                'data' => null,
                'error' => [
                    'code' => 'SYSTEM_ERROR',
                    'details' => $e->getMessage()
                ]
            ];
        }
    }
}
