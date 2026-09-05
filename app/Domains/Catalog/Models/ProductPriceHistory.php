<?php

namespace App\Domains\Catalog\Models;

use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceHistory extends Model
{
    use HasUuid;

    protected $table = 'product_price_histories';

    protected $fillable = [
        'product_id',
        'old_cost_price',
        'new_cost_price',
        'old_sell_price',
        'new_sell_price',
        'note',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'old_cost_price' => 'decimal:2',
            'new_cost_price' => 'decimal:2',
            'old_sell_price' => 'decimal:2',
            'new_sell_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
