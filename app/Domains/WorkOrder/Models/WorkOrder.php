<?php

namespace App\Domains\WorkOrder\Models;

use App\Domains\CustomerVehicle\Models\Customer;
use App\Domains\CustomerVehicle\Models\Vehicle;
use App\Domains\MasterData\Models\Branch;
use App\Domains\POS\Models\Invoice;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'vehicle_id',
        'status',
        'odometer',
        'complaint',
        'assigned_mechanic_id',
        'price_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'status' => WorkOrderStatus::class,
            'odometer' => 'integer',
            'price_snapshot' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mechanic_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkOrderAssignment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
