<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email'];

    public function establishments()
    {
        return $this->hasMany(Establishment::class);
    }
}
