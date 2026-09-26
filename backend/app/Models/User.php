<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'passport',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'require_password_change',
        'is_active',
        'email_verified_at',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'require_password_change' => 'boolean',
            'is_active' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }
    /**
     * Determine if the user has 2FA enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get the recovery codes for the user.
     */
    public function recoveryCodes(): array
    {
        return $this->two_factor_recovery_codes 
            ? json_decode(decrypt($this->two_factor_recovery_codes), true) 
            : [];
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        }

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        return $codes;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        \Illuminate\Support\Facades\Mail::to($this)->send(new \App\Mail\PasswordResetRequestMail($this, $token));
    }

    /**
     * Check if user is exempt from 2FA
     */
    public function isTwoFactorExempt(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function createdEstablishments()
    {
        return $this->hasMany(Establishment::class, 'created_by');
    }

    public function designatedAreas()
    {
        return $this->hasMany(UserDesignatedArea::class);
    }

    public function getDesignatedLgasAndWards()
    {
        if ($this->hasRole('super-admin')) {
            return Lga::with('wards')->orderBy('name')->get();
        }

        $designated = $this->designatedAreas()->with(['lga', 'ward'])->get();
        if ($designated->isEmpty()) {
            return collect();
        }

        $lgaMap = [];
        foreach ($designated as $area) {
            if (!$area->lga) continue;
            
            $lgaId = $area->lga_id;
            if (!isset($lgaMap[$lgaId])) {
                $lgaMap[$lgaId] = [
                    'lga' => $area->lga,
                    'all_wards' => false,
                    'ward_ids' => []
                ];
            }
            
            if (is_null($area->ward_id)) {
                $lgaMap[$lgaId]['all_wards'] = true;
            } else {
                $lgaMap[$lgaId]['ward_ids'][] = $area->ward_id;
            }
        }

        $result = collect();
        foreach ($lgaMap as $lgaId => $info) {
            $lga = $info['lga'];
            if ($info['all_wards']) {
                $lga->setRelation('wards', $lga->wards()->orderBy('name')->get());
            } else {
                $lga->setRelation('wards', $lga->wards()->whereIn('id', $info['ward_ids'])->orderBy('name')->get());
            }
            $result->push($lga);
        }

        return $result->sortBy('name')->values();
    }
}
