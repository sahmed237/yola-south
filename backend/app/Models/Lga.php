<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lga extends Model
{
    protected $fillable = ['name'];

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}
