<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;

class Order extends Model
{
    use Auditable;
    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'discount_amount',
        'shipping_charge',
        'tax_amount',
        'total_amount',
        'shipping_address',
        'note',
        'admin_note',
        'cancelled_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->order_number) {
                return;
            }

            $year = now()->format('Y');
            $nextId = (int) DB::table('orders')->max('id') + 1;
            $order->order_number = sprintf('ORD-%s-%05d', $year, $nextId);
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->latest('created_at');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class)->latest('created_at');
    }

    public function latestRefund()
    {
        return $this->hasOne(Refund::class)->latestOfMany('created_at');
    }
}
