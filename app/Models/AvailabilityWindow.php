<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilityWindow extends Model
{
    use HasFactory;

    protected $fillable = [
        'clerk_id',
        'date',
        'start_time',
        'end_time',
        'slot_interval_minutes',
        'capacity_per_slot',
        'is_active',
        'remarks',
    ];

    /**
     * Find the active availability window for a given local date (Y-m-d).
     */
    public static function forDate(string $date, ?int $clerkId = null): ?self
    {
        $query = static::query()->where('date', $date)->where('is_active', true);
        if ($clerkId) {
            $query->where(function ($q) use ($clerkId) {
                $q->whereNull('clerk_id')->orWhere('clerk_id', $clerkId);
            });
        }
        // Prefer clerk-specific, then global
        return $query->orderByRaw('CASE WHEN clerk_id IS NULL THEN 1 ELSE 0 END')->first();
    }
}