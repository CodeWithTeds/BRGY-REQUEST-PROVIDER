<?php

namespace App\Repositories;

use App\Models\BarangayPermit;

class BussinessPermitRepository extends Repository
{
    public function __construct(protected BarangayPermit $barangayPermit)
    {   
        parent::__construct($barangayPermit);
    }
}
