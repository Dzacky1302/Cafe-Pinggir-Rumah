<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'order_type', 'customer_name', 'phone', 'table_number',
        'person_count', 'pickup_time', 'notes', 'items', 'total',
        'status', 'payment_method', 'payment_type', 'fraud_status',
        'midtrans_token', 'midtrans_transaction_id', 'paid_at', 'expired_at',
    ];

    protected $casts = [
        'items' => 'array',
        'pickup_time' => 'datetime:H:i',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    // Route model binding by order_id string
    public function getRouteKeyName(): string
    {
        return 'order_id';
    }

    // Helpers
    public function getTotalRupiahAttribute(): string
    {
        return number_format($this->total / 100, 0, ',', '.');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            OrderStatus::PENDING => '#f59e0b',
            OrderStatus::PAID => '#3b82f6',
            OrderStatus::PROCESSING => '#8b5cf6',
            OrderStatus::DONE => '#10b981',
            OrderStatus::CANCELLED => '#ef4444',
            OrderStatus::EXPIRED => '#9ca3af',
            OrderStatus::REFUNDED => '#14b8a6',
            default => '#6b7280',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            OrderStatus::PENDING => 'Menunggu Pembayaran',
            OrderStatus::PAID => 'Sudah Dibayar',
            OrderStatus::PROCESSING => 'Sedang Diproses',
            OrderStatus::DONE => 'Selesai',
            OrderStatus::CANCELLED => 'Dibatalkan',
            OrderStatus::EXPIRED => 'Kedaluwarsa',
            OrderStatus::REFUNDED => 'Dana Dikembalikan',
            default => ucfirst($this->status),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->order_type === 'dine_in' ? 'Dine In' : 'Take Away';
    }

    public function getTypeIconAttribute(): string
    {
        return $this->order_type === 'dine_in' ? 'fa-utensils' : 'fa-bag-shopping';
    }
}
