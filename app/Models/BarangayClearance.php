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
}