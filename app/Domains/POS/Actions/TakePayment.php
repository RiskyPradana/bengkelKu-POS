<?php

namespace App\Domains\POS\Actions;

use App\Domains\POS\Models\Payment;

class TakePayment
{
    public function handle(array $data): Payment
    {
        return Payment::create($data);
    }
}
