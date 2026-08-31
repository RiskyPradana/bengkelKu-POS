<?php

namespace App\Domains\Inventory\Models;

use App\Domains\MasterData\Models\Branch;
use App\Domains\Catalog\Models\Product;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasUuid;

    protected $fillable = [
        'branch_id', 'product_id', 'type', 'quantity',
        'stock_before', 'stock_after', 'reference', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'stock_before' => 'integer',
            'stock_after'  => 'integer',
        ];
    }

    public function branch(): BelongsTo   { return $this->belongsTo(Branch::class); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }
}
