<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateOfIndigency extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purpose',
        'status',
        'remarks',
        'application_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supportingDocuments()
    {
        return $this->hasMany(SupportingDocument::class);
    }
}