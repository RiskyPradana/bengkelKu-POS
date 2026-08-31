<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Models\Product;

class CreateProduct
{
    public function handle(array $data): Product
    {
        return Product::create($data);
    }
}
