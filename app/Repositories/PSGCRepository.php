<?php

namespace App\Repositories;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class PSGCRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(new Barangay());
    }

    public function getBarangaysByCity(string $code)
    {
        return $this->query()
            ->select('code', 'name')
            ->where('city_code', $code)
            ->orderBy('name')
            ->get();
    }

    public function getBarangaysByIslandGroup()
    {
        return $this->query()
            ->orderBy('name')
            ->get(['code', 'name']);
    }

    public function getRegions()
    {
        return Region::query()
            ->orderBy('name')
            ->get(['code', 'name']);
    }

    public function getProvincesByRegion(string $code)
    {
        return (new Repository(new Province()))
            ->query()
            ->select('code', 'name')
            ->where('region_code', $code)
            ->orderBy('name')
            ->get();
    }

    public function getCitiesByProvince(string $code)
    {
        return (new Repository(new City()))
            ->query()
            ->select('code', 'name')
            ->where('province_code', $code)
            ->orderBy('name')
            ->get();
    }

    public function getCitiesByRegion(string $code)
    {
        return (new Repository(new City()))
            ->query()
            ->select('code', 'name')
            ->where('region_code', $code)
            ->orderBy('name')
            ->get();
    }

    public function getBarangayById(string $code)
    {
        return DB::table('psgc_barangays')
            ->where('brgy_code', $code)
            ->value('id');
    }
}