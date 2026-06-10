<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ProductStock extends Model
{
    use Auditable;

    protected $fillable = [
        'product_id',
        'color_id',
        'attribute_value_id',
        'sku',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }

    /**
     * Get the total available stock for a product
     */
    public static function getTotalStockForProduct($productId)
    {
        return self::where('product_id', $productId)->sum('quantity');
    }

    /**
     * Check if stock is available
     */
    public function isAvailable($requestedQty = 1): bool
    {
        return $this->quantity >= $requestedQty;
    }

    /**
     * Reduce stock quantity
     */
    public function reduceStock($quantity): bool
    {
        if ($this->quantity >= $quantity) {
            $this->update(['quantity' => $this->quantity - $quantity]);
            return true;
        }
        return false;
    }

    /**
     * Increase stock quantity
     */
    public function increaseStock($quantity): void
    {
        $this->update(['quantity' => $this->quantity + $quantity]);
    }
}
