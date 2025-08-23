<?php

namespace App\Repositories;

use App\Models\User;

class BussinessPermitRepository extends Repository {
    
    protected $user;

    public function __construct(User $user)
    {
        parent::__construct($user);
        
        $this->user = $user;
    }
}
