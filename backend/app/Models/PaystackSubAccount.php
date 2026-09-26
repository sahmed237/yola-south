<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class PaystackSubAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'subaccount_code',
        'active',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'active' => 'boolean',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
