<?php

namespace App\Domains\Inventory\Models;

use App\Domains\MasterData\Models\Branch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterBranchTransfer extends Model
{
    use HasUuid;

    protected $fillable = [
        'transfer_number', 'from_branch_id', 'to_branch_id', 'status',
        'notes', 'requested_by', 'approved_by', 'approved_at', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function fromBranch(): BelongsTo { return $this->belongsTo(Branch::class, 'from_branch_id'); }
    public function toBranch(): BelongsTo   { return $this->belongsTo(Branch::class, 'to_branch_id'); }
    public function items(): HasMany        { return $this->hasMany(InterBranchTransferItem::class, 'transfer_id'); }

    public static function generateNumber(): string
    {
        $count = static::whereYear('created_at', now()->year)->count() + 1;
        return 'TRF-' . now()->format('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
