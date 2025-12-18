<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'amount',
        'paid_amount',
        'paid',
        'payment_date',
        'payment_method',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid' => 'boolean',
        'payment_date' => 'date',
    ];

    /**
     * Get the transaction that owns the fine.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get remaining amount to be paid.
     */
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    /**
     * Check if fine is fully paid.
     */
    public function isFullyPaid()
    {
        return $this->paid && $this->paid_amount >= $this->amount;
    }

    /**
     * Scope a query to only include unpaid fines.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('paid', false)
                    ->orWhereColumn('paid_amount', '<', 'amount');
    }
}
