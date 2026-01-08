<?php

namespace App\GraphQL\Mutations;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReturnBook
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

            $transaction = Transaction::find($args['transaction_id']);

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                    'data' => null,
                    'error' => [
                        'code' => 'TRANSACTION_NOT_FOUND',
                        'details' => 'Transaction ID tidak ditemukan'
                    ]
                ];
            }

            if ($user->role !== 'admin' && $transaction->user_id !== $user->id) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized',
                    'data' => null,
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'details' => 'Not your transaction'
                    ]
                ];
            }

            if ($transaction->return_date) {
                return [
                    'success' => false,
                    'message' => 'Book already returned',
                    'data' => null,
                    'error' => [
                        'code' => 'ALREADY_RETURNED',
                        'details' => 'Transaction sudah selesai'
                    ]
                ];
            }

            $transaction->update([
                'return_date' => Carbon::now(),
                'status' => 'returned'
            ]);

            $transaction->book->increment('available_stock');

            return [
                'success' => true,
                'message' => 'Book returned successfully',
                'data' => $transaction->load(['book', 'user']),
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to return book',
                'data' => null,
                'error' => [
                    'code' => 'SYSTEM_ERROR',
                    'details' => $e->getMessage()
                ]
            ];
        }
    }
}
