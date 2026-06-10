<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Color extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'code'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_color');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }
}
