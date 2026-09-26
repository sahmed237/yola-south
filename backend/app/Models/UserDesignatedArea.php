<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDesignatedArea extends Model
{
    protected $fillable = ['user_id', 'lga_id', 'ward_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }
}
