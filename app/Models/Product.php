<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'variant',
        'price',
        'image',
    ];

    // Price in cents helper
    public function getPriceDisplayAttribute()
    {
        return number_format($this->price / 100, 2);
    }
}
