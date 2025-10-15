<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_at',
        'status',
        'remarks',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
    ];

    public function appointable(): MorphTo
    {
        return $this->morphTo();
    }
}