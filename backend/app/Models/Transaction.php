<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id',
        'book_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'notes',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-update book availability when transaction is created
        static::created(function ($transaction) {
            if ($transaction->status === 'borrowed') {
                $transaction->book->decrement('available_copies');
            }
        });

        // Auto-update book availability when book is returned
        static::updated(function ($transaction) {
            if ($transaction->isDirty('status')) {
                if ($transaction->status === 'returned' &&
                    in_array($transaction->getOriginal('status'), ['borrowed', 'overdue'])) {
                    $transaction->book->increment('available_copies');
                }
            }
        });
    }

    /**
     * Get the member that owns the transaction.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the book that is borrowed.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the librarian who approved this transaction.
     */
    public function approvedBy()
    {
        return $this->belongsTo(Member::class, 'approved_by');
    }

    /**
     * Get the fine for this transaction.
     */
    public function fine()
    {
        return $this->hasOne(Fine::class);
    }

    /**
     * Check if transaction is overdue.
     */
    public function isOverdue()
    {
        return $this->status === 'borrowed' &&
               Carbon::parse($this->due_date)->isPast();
    }

    /**
     * Calculate days overdue.
     */
    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return Carbon::parse($this->due_date)->diffInDays(now());
    }

    /**
     * Calculate fine amount (Rp 1000 per day).
     */
    public function calculateFine()
    {
        $daysOverdue = $this->getDaysOverdue();
        return $daysOverdue * 1000; // Rp 1000 per hari
    }

    /**
     * Scope a query to only include active borrows.
     */
    public function scopeBorrowed($query)
    {
        return $query->whereIn('status', ['borrowed', 'overdue']);
    }

    /**
     * Scope a query to only include overdue transactions.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->where('status', 'borrowed')
                          ->where('due_date', '<', now());
                    });
    }
}
