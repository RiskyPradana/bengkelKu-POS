<?php

namespace App\Domains\CustomerVehicle\Models;

use App\Domains\WorkOrder\Models\WorkOrder;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'customer_id',
        'plate_number',
        'brand',
        'type',
        'year',
        'last_mileage',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_mileage' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
