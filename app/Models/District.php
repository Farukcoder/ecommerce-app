<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class District extends Model
{
    use Auditable;

    protected $fillable = ['id', 'division_id', 'name', 'bn_name', 'lat', 'long'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function upazilas()
    {
        return $this->hasMany(Upazila::class);
    }
}
