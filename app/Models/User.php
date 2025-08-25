<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function applicantProfile()
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function supportingDocuments()
    {
        return $this->hasMany(SupportingDocument::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

  

    public function city()
    {
        return $this->hasOneThrough(City::class, Address::class, 'user_id', 'code', 'id', 'city_code')
            ->where('addresses.type', 'present');
    }

    public function province()
    {
        return $this->hasOneThrough(Province::class, Address::class, 'user_id', 'code', 'id', 'province_code')
            ->where('addresses.type', 'present');
    }

    public function region()
    {
        return $this->hasOneThrough(Region::class, Address::class, 'user_id', 'code', 'id', 'region_code')
            ->where('addresses.type', 'present');
    }
}
