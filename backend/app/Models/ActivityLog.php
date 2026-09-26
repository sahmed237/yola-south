<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'establishment_id',
        'user_id',
        'action_type',
        'remarks',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
