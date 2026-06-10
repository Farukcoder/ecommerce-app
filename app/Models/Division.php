<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Division extends Model
{
    use Auditable;

    protected $fillable = ['id', 'name', 'bn_name', 'lat', 'long'];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
