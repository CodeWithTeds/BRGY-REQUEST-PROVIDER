<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_at',
        'status',
        'remarks',
        'availability_window_id',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
    ];

    public function appointable(): MorphTo
    {
        return $this->morphTo();
    }

    public function availabilityWindow(): BelongsTo
    {
        return $this->belongsTo(AvailabilityWindow::class);
    }
}