<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConstant extends Model
{
    protected $fillable = [
        'category',
        'key',
        'value',
        'created_by',
        'updated_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
