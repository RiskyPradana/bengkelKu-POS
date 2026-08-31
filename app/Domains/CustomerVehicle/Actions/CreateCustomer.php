<?php

namespace App\Domains\CustomerVehicle\Actions;

use App\Domains\CustomerVehicle\Models\Customer;

class CreateCustomer
{
    public function handle(array $data): Customer
    {
        return Customer::create($data);
    }
}
