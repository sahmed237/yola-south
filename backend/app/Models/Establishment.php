<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'name',
        'establishment_type_id',
        'establishment_size_id',
        'lga',
        'ward',
        'lat',
        'lng',
        'inside_metropolis',
        'street_address',
        'house_number',
        'city',
        'postal_code',
        'status',
        'occupant_id',
        'owner_id',
        'qr_code_path',
        'created_by',
        'base_year'
    ];

    protected $casts = [
        'inside_metropolis' => 'boolean',
        'base_year' => 'integer',
    ];

    public function establishmentType()
    {
        return $this->belongsTo(EstablishmentType::class, 'establishment_type_id');
    }

    public function establishmentSize()
    {
        return $this->belongsTo(EstablishmentSize::class, 'establishment_size_id');
    }

    public function owner()
    {
        return $this->belongsTo(EstablishmentOwner::class, 'owner_id');
    }

    public function occupant()
    {
        return $this->belongsTo(Occupant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function images()
    {
        return $this->hasMany(EstablishmentImage::class);
    }

    public function updateRequests()
    {
        return $this->hasMany(EstablishmentUpdateRequest::class);
    }

    public function scopeAreaRestricted($query)
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('super-admin')) {
            return $query;
        }

        $designated = \Illuminate\Support\Facades\DB::table('user_designated_areas')
            ->where('user_id', $user->id)
            ->get();

        if ($designated->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $lgaIds = $designated->pluck('lga_id')->unique()->toArray();
        $wardIds = $designated->pluck('ward_id')->filter()->unique()->toArray();

        $lgas = \App\Models\Lga::whereIn('id', $lgaIds)->pluck('name', 'id')->toArray();
        $wards = \App\Models\Ward::whereIn('id', $wardIds)->pluck('name', 'id')->toArray();

        return $query->where(function ($q) use ($designated, $lgas, $wards) {
            foreach ($designated as $area) {
                $lgaName = $lgas[$area->lga_id] ?? null;
                if (!$lgaName) continue;

                $q->orWhere(function ($sq) use ($area, $lgaName, $wards) {
                    if (is_null($area->ward_id)) {
                        $sq->where('lga', $lgaName);
                    } else {
                        $wardName = $wards[$area->ward_id] ?? null;
                        if ($wardName) {
                            $sq->where('lga', $lgaName)->where('ward', $wardName);
                        } else {
                            $sq->whereRaw('1 = 0');
                        }
                    }
                });
            }
        });
    }
}
