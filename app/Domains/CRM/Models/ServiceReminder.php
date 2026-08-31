<?php

namespace App\Domains\CRM\Models;

use App\Domains\CustomerVehicle\Models\Customer;
use App\Domains\CustomerVehicle\Models\Vehicle;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReminder extends Model
{
    use HasUuid;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_SENT      = 'sent';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'vehicle_id', 'customer_id', 'trigger_type',
        'interval_days', 'mileage_interval',
        'last_service_date', 'last_service_mileage',
        'next_reminder_date', 'next_reminder_mileage',
        'is_active',
        // Kolom hasil patch migration 000006
        'status', 'due_date', 'sent_at', 'notes', 'attempt_count',
    ];

    protected function casts(): array
    {
        return [
            'last_service_date'     => 'date',
            'next_reminder_date'    => 'date',
            'due_date'              => 'date',
            'sent_at'               => 'datetime',
            'is_active'             => 'boolean',
            'interval_days'         => 'integer',
            'mileage_interval'      => 'integer',
            'last_service_mileage'  => 'integer',
            'next_reminder_mileage' => 'integer',
            'attempt_count'         => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * due_date adalah sumber utama. Kalau kosong, jatuh balik ke
     * next_reminder_date supaya data lama tetap terbaca.
     */
    public function getEffectiveDueDateAttribute()
    {
        return $this->due_date ?? $this->next_reminder_date;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDueBefore(Builder $query, $date): Builder
    {
        return $query->where(function (Builder $q) use ($date): void {
            $q->whereDate('due_date', '<=', $date)
              ->orWhere(function (Builder $inner) use ($date): void {
                  $inner->whereNull('due_date')
                        ->whereDate('next_reminder_date', '<=', $date);
              });
        });
    }

    public function isDue(): bool
    {
        $due = $this->effective_due_date;

        return $due !== null && now()->gte($due);
    }

    public function markSent(): void
    {
        $this->update([
            'status'        => self::STATUS_SENT,
            'sent_at'       => now(),
            'attempt_count' => (int) $this->attempt_count + 1,
        ]);
    }

    public function markFailed(?string $reason = null): void
    {
        $this->update([
            'status'        => self::STATUS_FAILED,
            'notes'         => $reason,
            'attempt_count' => (int) $this->attempt_count + 1,
        ]);
    }
}
