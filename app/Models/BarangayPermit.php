<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangayPermit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'remarks',
        'application_date',
        'appointment_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applicantProfile()
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    // Additional relationship to retrieve ALL addresses linked to this permit
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function supportingDocument()
    {
        return $this->hasOne(SupportingDocument::class);
    }

    // Additional relationship to retrieve ALL supporting documents linked to this permit
    public function supportingDocuments()
    {
        return $this->hasMany(SupportingDocument::class);
    }

    // Shared appointments across all document requests
    public function appointments()
    {
        return $this->morphMany(Appointment::class, 'appointable');
    }
}
