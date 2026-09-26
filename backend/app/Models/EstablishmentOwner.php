<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstablishmentOwner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'gender',
        'nin'
    ];

    public function establishments()
    {
        return $this->hasMany(Establishment::class, 'owner_id');
    }
}
