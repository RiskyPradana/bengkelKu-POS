<?php

namespace App\Livewire\Settings;

use App\Domains\MasterData\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class BranchManagement extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?string $editingId = null;

    public string $name = '';
    public string $code = '';
    public string $address = '';
    public string $phone = '';
    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:3', 'max:100'],
            'code'    => [
                'required', 'string', 'max:20',
                Rule::unique('branches', 'code')->ignore($this->editingId),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:25'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name'    => 'nama cabang',
            'code'    => 'kode cabang',
            'address' => 'alamat',
            'phone'   => 'telepon',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $branch = Branch::findOrFail($id);

        $this->editingId = (string) $branch->id;
        $this->name      = (string) $branch->name;
        $this->code      = (string) $branch->code;
        $this->address   = (string) ($branch->address ?? '');
        $this->phone     = (string) ($branch->phone ?? '');
        $this->isActive  = (bool) $branch->is_active;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => trim($this->name),
            'code'      => strtoupper(trim($this->code)),
            'address'   => $this->address !== '' ? $this->address : null,
            'phone'     => $this->phone !== '' ? $this->phone : null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            $branch = Branch::findOrFail($this->editingId);
            $branch->update($data);
            session()->flash('sukses', 'Cabang ' . $branch->name . ' berhasil diperbarui.');
        } else {
            Branch::create($data);
            session()->flash('sukses', 'Cabang ' . $data['name'] . ' berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(string $id): void
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['is_active' => ! $branch->is_active]);

        session()->flash('sukses', $branch->name . ' sekarang ' . ($branch->is_active ? 'AKTIF' : 'NONAKTIF') . '.');
    }

    public function deleteBranch(string $id): void
    {
        $branch = Branch::findOrFail($id);

        if ($this->isLastBranch()) {
            session()->flash('gagal', 'Ini satu-satunya cabang. Tidak bisa dihapus.');

            return;
        }

        if ($this->hasUsageData($id)) {
            $branch->update(['is_active' => false]);
            session()->flash('sukses', $branch->name . ' punya riwayat transaksi/stok, jadi dinonaktifkan (bukan dihapus) agar data lama tetap utuh.');

            return;
        }

        $name = $branch->name;
        $branch->delete();
        session()->flash('sukses', 'Cabang ' . $name . ' berhasil dihapus.');
    }

    private function isLastBranch(): bool
    {
        return Branch::count() <= 1;
    }

    private function hasUsageData(string $branchId): bool
    {
        $checks = [
            ['table' => 'users', 'column' => 'branch_id'],
            ['table' => 'work_orders', 'column' => 'branch_id'],
            ['table' => 'invoices', 'column' => 'branch_id'],
            ['table' => 'branch_stocks', 'column' => 'branch_id'],
            ['table' => 'purchase_orders', 'column' => 'branch_id'],
        ];

        foreach ($checks as $check) {
            try {
                if (! Schema::hasTable($check['table']) || ! Schema::hasColumn($check['table'], $check['column'])) {
                    continue;
                }

                if (DB::table($check['table'])->where($check['column'], $branchId)->exists()) {
                    return true;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name      = '';
        $this->code      = '';
        $this->address   = '';
        $this->phone     = '';
        $this->isActive  = true;

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.settings.branch-management', [
            'branches' => Branch::query()->orderBy('name')->paginate(15),
        ])->layout('layouts.app', ['title' => 'Manajemen Cabang']);
    }
}
