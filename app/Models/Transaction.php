<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'quantity',
        'transaction_date',
    ];

    /**
     * Get the product that owns the transaction.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
