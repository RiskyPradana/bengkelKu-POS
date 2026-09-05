<?php

namespace App\Domains\Catalog\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceLevel extends Model
{
    use HasUuid;

    protected $fillable = ['product_id', 'level_no', 'level_name', 'price'];

    protected function casts(): array
    {
        return [
            'level_no' => 'integer',
            'price'    => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
