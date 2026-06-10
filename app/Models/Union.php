<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Union extends Model
{
    use Auditable;

    protected $fillable = ['id', 'upazila_id', 'name', 'bn_name', 'url'];

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }
}
