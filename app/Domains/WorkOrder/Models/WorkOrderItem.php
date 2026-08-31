<?php

namespace App\Domains\WorkOrder\Models;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ServiceItem;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class WorkOrderItem extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'work_order_id',
        'item_type',
        'product_id',
        'service_item_id',
        'name',
        'qty',
        'unit_price',
        'subtotal',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'snapshot' => 'array',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function isProduct(): bool
    {
        return $this->item_type === 'product';
    }

    public function isService(): bool
    {
        return $this->item_type === 'service';
    }
}
