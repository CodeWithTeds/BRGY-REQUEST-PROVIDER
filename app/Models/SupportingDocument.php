<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'verified',
        'barangay_permit_id',
        'barangay_clearance_id'
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barangayPermit()
    {
        return $this->belongsTo(BarangayPermit::class);
    }

    public function barangayClearance()
    {
        return $this->belongsTo(BarangayClearance::class);
    }
}