<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'device_name',
        'platform',
        'browser',
        'isp',
        'mac_address',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to log an authentication event
     */
    public static function log($user, $action, $payload = [])
    {
        $agent = request()->userAgent();
        $ip = request()->ip();
        
        // Basic parsing (could use a package for better results)
        $browser = 'Unknown';
        if (str_contains($agent, 'MSIE')) $browser = 'Internet Explorer';
        elseif (str_contains($agent, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($agent, 'Chrome')) $browser = 'Chrome';
        elseif (str_contains($agent, 'Safari')) $browser = 'Safari';
        elseif (str_contains($agent, 'Opera')) $browser = 'Opera';

        $platform = 'Unknown';
        if (str_contains($agent, 'Windows')) $platform = 'Windows';
        elseif (str_contains($agent, 'Macintosh')) $platform = 'Mac OS';
        elseif (str_contains($agent, 'Linux')) $platform = 'Linux';
        elseif (str_contains($agent, 'iPhone')) $platform = 'iOS';
        elseif (str_contains($agent, 'Android')) $platform = 'Android';

        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'ip_address' => $ip,
            'user_agent' => $agent,
            'browser' => $browser,
            'platform' => $platform,
            'device_name' => $platform, // Basic device name
            'isp' => null, // Would require external API
            'payload' => $payload,
        ]);
    }
}
