<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstablishmentUpdateRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'establishment_id',
        'user_id',
        'reason',
        'status',
        'approver_id',
        'approval_remarks',
        'approved_at',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
