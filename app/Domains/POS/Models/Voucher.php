<?php

namespace App\Domains\POS\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasUuid;

    protected $fillable = [
        'code', 'type', 'value', 'min_purchase', 'max_discount',
        'usage_limit', 'used_count', 'valid_from', 'valid_until', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'        => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit'  => 'integer',
            'used_count'   => 'integer',
            'valid_from'   => 'date',
            'valid_until'  => 'date',
            'is_active'    => 'boolean',
        ];
    }

    /**
     * Cek apakah voucher masih boleh dipakai untuk subtotal tertentu.
     * $reason diisi pesan alasan penolakan (untuk ditampilkan ke kasir).
     */
    public function isCurrentlyValid(float $subtotal, ?string &$reason = null): bool
    {
        if (! $this->is_active) {
            $reason = 'Voucher tidak aktif.';
            return false;
        }
        if ($this->valid_from && now()->startOfDay()->lt($this->valid_from)) {
            $reason = 'Voucher belum berlaku.';
            return false;
        }
        if ($this->valid_until && now()->endOfDay()->gt($this->valid_until)) {
            $reason = 'Voucher sudah kedaluwarsa.';
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            $reason = 'Kuota pemakaian voucher sudah habis.';
            return false;
        }
        if ($subtotal < (float) $this->min_purchase) {
            $reason = 'Minimal belanja Rp ' . number_format((float) $this->min_purchase, 0, ',', '.') . ' belum tercapai.';
            return false;
        }
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
