<?php

namespace App\Domains\Inventory\Models;

use App\Domains\MasterData\Models\Branch;
use App\Domains\Catalog\Models\Product;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchStock extends Model
{
    use HasUuid;

    protected $fillable = [
        'branch_id', 'product_id', 'quantity', 'min_stock', 'rack_location',
    ];

    protected function casts(): array
    {
        return [
            'quantity'  => 'integer',
            'min_stock' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }
}
