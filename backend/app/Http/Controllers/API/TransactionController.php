<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Book;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role === 'admin') {
            $transactions = Transaction::with(['user', 'book'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $transactions = Transaction::with('book')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'last_page' => $transactions->lastPage(),
            ]
        ]);
    }

    public function borrow(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $book = Book::findOrFail($request->book_id);

        // Check stock
        if ($book->available_stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Book is not available'
            ], 400);
        }

        // Check if user already borrowed this book
        $activeBorrow = Transaction::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->whereNull('return_date')
            ->first();

        if ($activeBorrow) {
            return response()->json([
                'success' => false,
                'message' => 'You already borrowed this book'
            ], 400);
        }

        $borrowDuration = config('library.borrow_duration', 14);
        $borrowDate = Carbon::now();
        $dueDate = Carbon::now()->addDays($borrowDuration);

        // Create transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'status' => 'borrowed'
        ]);

        // Update stock
        $book->decrement('available_stock');

        $finePerDay = config('library.fine_per_day', 5000);

        return response()->json([
            'success' => true,
            'message' => 'Book borrowed successfully',
            'data' => [
                'transaction' => $transaction->load('book'),
                'borrow_date' => $borrowDate->format('d M Y'),
                'due_date' => $dueDate->format('d M Y'),
                'fine_per_day' => 'Rp ' . number_format($finePerDay, 0, ',', '.'),
                'warning' => 'Please return before ' . $dueDate->format('d M Y') . ' to avoid fine'
            ]
        ], 201);
    }

    public function return($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $transaction = Transaction::with('book')->findOrFail($id);

        // Check ownership (non-admin can only return their own books)
        if ($user->role !== 'admin' && $transaction->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if already returned
        if ($transaction->return_date) {
            return response()->json([
                'success' => false,
                'message' => 'Book already returned'
            ], 400);
        }

        $returnDate = Carbon::now();
        $dueDate = Carbon::parse($transaction->due_date);

        // Calculate late days and fine
        $daysLate = 0;
        $fineAmount = 0;

        if ($returnDate->gt($dueDate)) {
            $daysLate = $returnDate->diffInDays($dueDate);
            $finePerDay = config('library.fine_per_day', 5000);
            $maxFine = config('library.max_fine', 100000);

            $fineAmount = min($daysLate * $finePerDay, $maxFine);
        }

        // Update transaction
        $transaction->update([
            'return_date' => $returnDate,
            'status' => 'returned',
            'days_late' => $daysLate,
            'fine_amount' => $fineAmount
        ]);

        // Update book stock
        $transaction->book->increment('available_stock');

        $message = $fineAmount > 0
            ? "Book returned late. Fine: Rp " . number_format($fineAmount, 0, ',', '.')
            : 'Book returned on time. Thank you!';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'transaction' => $transaction,
                'return_date' => $returnDate->format('d M Y'),
                'days_late' => $daysLate,
                'fine_amount' => $fineAmount,
                'fine_formatted' => 'Rp ' . number_format($fineAmount, 0, ',', '.')
            ]
        ]);
    }

    public function checkFine($id)
    {
        $transaction = Transaction::with('book')->findOrFail($id);

        if ($transaction->return_date) {
            return response()->json([
                'success' => true,
                'message' => 'Book already returned',
                'data' => [
                    'status' => 'returned',
                    'return_date' => Carbon::parse($transaction->return_date)->format('d M Y'),
                    'fine_paid' => $transaction->fine_amount,
                    'fine_formatted' => 'Rp ' . number_format($transaction->fine_amount, 0, ',', '.')
                ]
            ]);
        }

        $dueDate = Carbon::parse($transaction->due_date);
        $today = Carbon::now();

        if ($today->lte($dueDate)) {
            $daysRemaining = $today->diffInDays($dueDate);

            return response()->json([
                'success' => true,
                'message' => 'No fine yet',
                'data' => [
                    'status' => 'borrowed',
                    'due_date' => $dueDate->format('d M Y'),
                    'days_remaining' => $daysRemaining,
                    'current_fine' => 0
                ]
            ]);
        }

        $daysLate = $today->diffInDays($dueDate);
        $finePerDay = config('library.fine_per_day', 5000);
        $maxFine = config('library.max_fine', 100000);
        $estimatedFine = min($daysLate * $finePerDay, $maxFine);

        return response()->json([
            'success' => true,
            'message' => 'Book is overdue',
            'data' => [
                'status' => 'overdue',
                'due_date' => $dueDate->format('d M Y'),
                'days_late' => $daysLate,
                'estimated_fine' => $estimatedFine,
                'fine_formatted' => 'Rp ' . number_format($estimatedFine, 0, ',', '.'),
                'fine_per_day' => 'Rp ' . number_format($finePerDay, 0, ',', '.')
            ]
        ]);
    }
}
