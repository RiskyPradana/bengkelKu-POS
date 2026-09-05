<?php

namespace App\Domains\Catalog\Models;

use App\Domains\WorkOrder\Models\WorkOrderItem;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'cost_price',
        'sell_price',
        'is_active',
        'category',
        'margin_percent',
        'brand_id',
        'unit_id',
        'price_mode',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'is_active' => 'boolean',
            'margin_percent' => 'decimal:2',
        ];
    }

    public function workOrderItems(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // Sesi 14: beberapa model harga (Level Harga 2-4, mengikuti iPos 5).
    // Level 1 tetap memakai kolom sell_price yang sudah ada.
    public function priceLevels(): HasMany
    {
        return $this->hasMany(ProductPriceLevel::class)->orderBy('level_no');
    }
}
