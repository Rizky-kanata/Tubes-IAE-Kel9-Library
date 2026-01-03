<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'year',
        'total_stock',
        'available_stock'
    ];

    protected $casts = [
        'year' => 'integer',
        'total_stock' => 'integer',
        'available_stock' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
