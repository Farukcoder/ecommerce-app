<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Refund extends Model
{
    use Auditable;

    protected $fillable = [
        'order_id',
        'amount',
        'reason',
        'method',
        'status',
        'processed_by',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
