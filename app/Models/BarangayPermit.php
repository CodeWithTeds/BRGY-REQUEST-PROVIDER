<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangayPermit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_type',
        'business_name',
        'business_address',
        'owner_name',
        'contact_number',
        'email',
        'status',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
