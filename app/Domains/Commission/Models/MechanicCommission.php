<?php

namespace App\Domains\Commission\Models;

use App\Domains\WorkOrder\Models\WorkOrder;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Komisi mekanik per Work Order — profit stream 1 ("kendaraan masuk").
 * Satu baris dibuat otomatis oleh CommissionService setiap kali sebuah
 * Work Order berpindah status ke Paid.
 */
class MechanicCommission extends Model
{
    use HasUuid;

    protected $table = 'mechanic_commissions';

    protected $fillable = [
        'user_id',
        'work_order_id',
        'branch_id',
        'commission_amount',
        'commission_rate',
        'base_amount',
        'service_amount',
        'period',
        'source',
        'earned_at',
        'is_paid',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_amount' => 'decimal:2',
            'commission_rate'   => 'decimal:2',
            'base_amount'       => 'decimal:2',
            'service_amount'    => 'decimal:2',
            'earned_at'         => 'datetime',
            'is_paid'           => 'boolean',
            'paid_at'           => 'datetime',
        ];
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
