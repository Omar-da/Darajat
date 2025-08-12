<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    public $timestamps = false;

    public function moreDetails()
    {
        return $this->hasMany(MoreDetail::class);
    }
}
