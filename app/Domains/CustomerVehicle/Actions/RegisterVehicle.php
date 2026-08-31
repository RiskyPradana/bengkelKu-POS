<?php

namespace App\Domains\CustomerVehicle\Actions;

use App\Domains\CustomerVehicle\Models\Vehicle;

class RegisterVehicle
{
    public function handle(array $data): Vehicle
    {
        return Vehicle::create($data);
    }
}
