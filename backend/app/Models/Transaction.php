<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
        'days_late'
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'integer',
        'days_late' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Calculate fine
    public function calculateFine()
    {
        if (!$this->return_date || !$this->due_date) {
            return 0;
        }

        $dueDate = Carbon::parse($this->due_date);
        $returnDate = Carbon::parse($this->return_date);

        if ($returnDate->lte($dueDate)) {
            return 0;
        }

        $daysLate = $returnDate->diffInDays($dueDate);
        $finePerDay = config('library.fine_per_day', 5000);
        $maxFine = config('library.max_fine', 100000);

        $fine = $daysLate * $finePerDay;

        return min($fine, $maxFine);
    }
}
