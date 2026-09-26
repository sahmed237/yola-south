<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenueHead extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'revenue_heads';

    protected $fillable = [
        'agency_id',
        'name',
        'code',
        'amount',
        'frequency',
        'sql_rule',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'revenue_head_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'revenue_head_id');
    }

    /**
     * Calculate the amount for a specific establishment based on the rule.
     */
    public function calculateAmount(Establishment $establishment): float
    {
        if (empty($this->sql_rule)) {
            return (float) $this->amount;
        }

        // Standardize keys for easier matching
        $establishmentData = [
            'type' => strtolower($establishment->establishmentType->key ?? ''),
            'size' => strtolower($establishment->establishmentSize->key ?? ''),
            'inside_metropolis' => $establishment->inside_metropolis ? 'true' : 'false',
            'inside_metro' => $establishment->inside_metropolis ? 'true' : 'false', // support both inside_metro and inside_metropolis
            'lga' => strtolower($establishment->lga ?? ''),
            'ward' => strtolower($establishment->ward ?? ''),
            'city' => strtolower($establishment->city ?? ''),
        ];

        // The rule consists of parts separated by semicolons
        // Example: type=retail & size=large & inside_metro=true : 15000; type=retail : 10000; default: 5000
        $parts = explode(';', $this->sql_rule);

        foreach ($parts as $part) {
            if (!str_contains($part, ':')) {
                continue;
            }

            [$conditionStr, $valueStr] = explode(':', $part, 2);
            $conditionStr = trim($conditionStr);
            $value = (float) trim($valueStr);

            if (strtolower($conditionStr) === 'default') {
                return $value;
            }

            // Parse sub-conditions separated by '&' or 'and'
            $subConditions = preg_split('/\s+(?:&|and)\s+/i', $conditionStr);
            $allMatch = true;

            foreach ($subConditions as $sub) {
                $sub = trim($sub);
                
                // Support multiple operators: =, ==, !=
                if (preg_match('/^([a-zA-Z_]+)\s*(=|==|!=)\s*(.+)$/', $sub, $matches)) {
                    $key = strtolower(trim($matches[1]));
                    $operator = trim($matches[2]);
                    $targetVal = strtolower(trim($matches[3], " '\""));

                    if (!array_key_exists($key, $establishmentData)) {
                        $allMatch = false;
                        break;
                    }

                    $currentVal = $establishmentData[$key];

                    if ($operator === '=' || $operator === '==') {
                        if ($currentVal !== $targetVal) {
                            $allMatch = false;
                            break;
                        }
                    } elseif ($operator === '!=') {
                        if ($currentVal === $targetVal) {
                            $allMatch = false;
                            break;
                        }
                    }
                } else {
                    // Fallback to simple contains or simple equals
                    if (str_contains($sub, '=')) {
                        [$k, $v] = explode('=', $sub, 2);
                        $k = strtolower(trim($k));
                        $v = strtolower(trim($v, " '\""));
                        
                        if (array_key_exists($k, $establishmentData) && $establishmentData[$k] !== $v) {
                            $allMatch = false;
                            break;
                        }
                    } else {
                        $allMatch = false;
                        break;
                    }
                }
            }

            if ($allMatch) {
                return $value;
            }
        }

        return (float) $this->amount;
    }
}
