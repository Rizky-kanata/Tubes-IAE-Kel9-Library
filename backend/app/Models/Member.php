<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Member extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'membership_date',
        'status',
        'role',
        'avatar',
        'birth_date',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'membership_date' => 'date',
        'birth_date' => 'date',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
        ];
    }

    /**
     * Get the transactions for the member.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get active (borrowed) transactions.
     */
    public function activeBorrows()
    {
        return $this->transactions()
                    ->where('status', 'borrowed')
                    ->orWhere('status', 'overdue');
    }

    /**
     * Get overdue transactions.
     */
    public function overdueTransactions()
    {
        return $this->transactions()
                    ->where('status', 'overdue')
                    ->orWhere(function($query) {
                        $query->where('status', 'borrowed')
                              ->where('due_date', '<', now());
                    });
    }

    /**
     * Check if member is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if member is librarian or admin.
     */
    public function isLibrarian()
    {
        return in_array($this->role, ['librarian', 'admin']);
    }

    /**
     * Scope a query to only include active members.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
