<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Brand extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
