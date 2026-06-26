<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingCost extends Model
{
    protected $fillable = [
        'district_id',
        'cost',
        'is_free',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_free' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
