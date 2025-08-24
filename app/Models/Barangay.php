<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $table = 'psgc_barangays';

    protected $fillable = [
        'brgy_code',
        'brgy_name',
        'city_code',
        'city_name',
        'province_code',
        'province_name',
        'region_code',
        'region_name',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class, 'barangay_id');
    }
}