<?php

namespace App\Domains\POS\Models;

use App\Domains\MasterData\Models\Branch;
use App\Domains\POS\Enums\InvoiceStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'branch_id',
        'work_order_id',
        'invoice_number',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'status' => InvoiceStatus::class,
        ];
    }

    protected function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    protected function getOutstandingAmountAttribute(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->payments()->sum('amount'));
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
