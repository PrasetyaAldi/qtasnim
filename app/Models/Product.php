<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'product_type_id',
        'name',
    ];

    /**
     * Get the product type that owns the product.
     */
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Get the stock record associated with the product.
     */
    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    /**
     * Get the transactions for the product.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
