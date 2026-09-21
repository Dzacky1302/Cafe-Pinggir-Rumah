<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'variant',
        'price',
        'quantity',
        'subtotal',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Harga satuan dalam format Rupiah: "25.000"
     */
    public function getPriceDisplayAttribute(): string
    {
        return number_format($this->price, 0, ',', '.');
    }

    /**
     * Subtotal dalam format Rupiah: "50.000"
     */
    public function getSubtotalDisplayAttribute(): string
    {
        return number_format($this->subtotal, 0, ',', '.');
    }
}
