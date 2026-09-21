<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'variant',
        'price',
        'image',
        'status',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Harga dalam format Rupiah: "25.000"
     */
    public function getPriceDisplayAttribute(): string
    {
        return number_format($this->price, 0, ',', '.');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
