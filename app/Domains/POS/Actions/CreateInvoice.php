<?php

namespace App\Domains\POS\Actions;

use App\Domains\POS\Models\Invoice;

class CreateInvoice
{
    public function handle(array $data): Invoice
    {
        return Invoice::create($data);
    }
}
