<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\ServiceItem;

class CreateServiceItem
{
    public function handle(array $data): ServiceItem
    {
        return ServiceItem::create($data);
    }
}
