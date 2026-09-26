<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstablishmentImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'establishment_id',
        'image_path',
        'is_primary',
        'lat',
        'lng',
        'device_info'
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
