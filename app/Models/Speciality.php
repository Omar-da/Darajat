<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{
    public $timestamps = false;
    
    public function moreDetails()
    {
        return $this->hasMany(MoreDetail::class);
    }
}
