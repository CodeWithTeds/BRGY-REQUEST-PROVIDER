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
    ];

    protected $attributes = [
        'status' => 'pending',
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
