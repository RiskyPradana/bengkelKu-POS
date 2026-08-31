<?php

namespace App\Domains\Inventory\Models;

use App\Domains\Catalog\Models\Product;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterBranchTransferItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'transfer_id', 'product_id', 'quantity_requested', 'quantity_received',
    ];

    public function transfer(): BelongsTo { return $this->belongsTo(InterBranchTransfer::class); }
    public function product(): BelongsTo  { return $this->belongsTo(Product::class); }
}
