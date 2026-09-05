<?php

namespace App\Livewire\Settings;

use App\Domains\POS\Models\Voucher;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Sesi 14: kelola kode voucher/diskon yang bisa diterapkan langsung di
 * halaman Kasir (lihat App\Livewire\Pos\Cashier::applyVoucher()).
 */
class VoucherSettings extends Component
{
    public ?string $voucherId  = null;
    public string  $code       = '';
    public string  $type       = 'percent'; // percent | fixed
    public string  $value      = '';
    public string  $minPurchase = '0';
    public string  $maxDiscount = '';
    public string  $usageLimit  = '';
    public string  $validFrom   = '';
    public string  $validUntil  = '';
    public bool    $isActive    = true;

    public function render(): View
    {
        return view('livewire.settings.voucher-settings')
            ->layout('layouts.admin', [
                'title'     => 'Voucher — BengkelOS',
                'pageTitle' => 'Voucher & Diskon',
                'pageSub'   => 'Kelola kode voucher yang bisa diterapkan kasir saat transaksi',
            ]);
    }

    #[Computed]
    public function vouchers(): Collection
    {
        return Voucher::orderByDesc('created_at')->get();
    }

    public function openCreate(): void
    {
        $this->reset(['voucherId', 'code', 'value', 'maxDiscount', 'usageLimit', 'validFrom', 'validUntil']);
        $this->type        = 'percent';
        $this->minPurchase = '0';
        $this->isActive    = true;
        $this->resetErrorBag();
    }

    public function editVoucher(string $id): void
    {
        $v = Voucher::findOrFail($id);
        $this->voucherId   = $v->id;
        $this->code        = $v->code;
        $this->type        = $v->type;
        $this->value       = (string) (float) $v->value;
        $this->minPurchase = (string) (float) $v->min_purchase;
        $this->maxDiscount = $v->max_discount !== null ? (string) (float) $v->max_discount : '';
        $this->usageLimit  = $v->usage_limit !== null ? (string) $v->usage_limit : '';
        $this->validFrom   = $v->valid_from?->format('Y-m-d') ?? '';
        $this->validUntil  = $v->valid_until?->format('Y-m-d') ?? '';
        $this->isActive    = (bool) $v->is_active;
        $this->resetErrorBag();
    }

    public function saveVoucher(): void
    {
        $this->validate([
            'code'  => 'required|string|max:50',
            'type'  => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
        ], [], ['code' => 'kode voucher', 'value' => 'nilai voucher']);

        $code = strtoupper(trim($this->code));

        $exists = Voucher::where('code', $code)->when($this->voucherId, fn ($q) => $q->where('id', '!=', $this->voucherId))->exists();
        if ($exists) {
            $this->addError('code', 'Kode voucher sudah dipakai.');
            return;
        }

        $data = [
            'code'         => $code,
            'type'         => $this->type,
            'value'        => (float) $this->value,
            'min_purchase' => $this->minPurchase !== '' ? (float) $this->minPurchase : 0,
            'max_discount' => $this->maxDiscount !== '' ? (float) $this->maxDiscount : null,
            'usage_limit'  => $this->usageLimit !== '' ? (int) $this->usageLimit : null,
            'valid_from'   => $this->validFrom ?: null,
            'valid_until'  => $this->validUntil ?: null,
            'is_active'    => $this->isActive,
        ];

        if ($this->voucherId) {
            Voucher::findOrFail($this->voucherId)->update($data);
            $msg = 'Voucher diperbarui.';
        } else {
            $data['used_count'] = 0;
            Voucher::create($data);
            $msg = 'Voucher baru ditambahkan.';
        }

        $this->openCreate();
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function toggleVoucher(string $id): void
    {
        $v = Voucher::findOrFail($id);
        $v->update(['is_active' => ! $v->is_active]);
    }

    public function deleteVoucher(string $id): void
    {
        Voucher::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Voucher dihapus.');
    }
}
