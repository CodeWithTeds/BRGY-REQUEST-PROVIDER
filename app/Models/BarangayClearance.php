<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangayClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'purpose',
        'remarks',
        'application_date',
        'issue_date',
        'expiry_date',
        'clearance_number'
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'application_date' => 'date',
        'issue_date' => 'date',
        'expiry_date' => 'date',
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

    public function supportingDocument()
    {
        return $this->hasOne(SupportingDocument::class);
    }

    // Additional relationship to retrieve ALL supporting documents linked to this clearance
    public function supportingDocuments()
    {
        return $this->hasMany(SupportingDocument::class);
    }

    // Additional relationship to retrieve ALL addresses linked to this clearance
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    // Shared appointments across all document requests
    public function appointments()
    {
        return $this->morphMany(Appointment::class, 'appointable');
    }
}