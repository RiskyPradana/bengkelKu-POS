<?php

namespace App\Livewire\WorkOrder;

use App\Domains\CustomerVehicle\Models\Customer;
use App\Domains\CustomerVehicle\Models\Vehicle;
use App\Domains\MasterData\Models\Branch;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    // Filters
    public string $search       = '';
    public string $statusFilter = '';

    // Modal
    public bool $showModal = false;
    public bool $isEditing = false;

    // Form
    public ?string $editingId  = null;
    public string  $customerId = '';
    public string  $vehicleId  = '';
    public string  $status     = 'Pending';
    public string  $odometer   = '';
    public string  $complaint  = '';
    public string  $mechanicId = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'customerId' => 'required',
            'vehicleId'  => 'required',
            'status'     => 'required',
            'complaint'  => 'required|min:3',
        ];
    }

    protected function messages(): array
    {
        return [
            'customerId.required' => 'Pelanggan wajib dipilih.',
            'vehicleId.required'  => 'Kendaraan wajib dipilih.',
            'complaint.required'  => 'Deskripsi keluhan wajib diisi.',
            'complaint.min'       => 'Keluhan minimal 3 karakter.',
        ];
    }

    public function render(): View
    {
        return view('livewire.work-order.index')
            ->layout('layouts.admin', [
                'title'     => 'Work Order — BengkelOS',
                'pageTitle' => 'Work Order (SPK)',
                'pageSub'   => 'Kelola Surat Perintah Kerja servis kendaraan',
            ]);
    }

    public function getWorkOrdersProperty(): Collection
    {
        $query = WorkOrder::with(['customer', 'vehicle', 'invoice'])->latest();

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s): void {
                $q->whereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('vehicle', fn ($q2) => $q2->where('plate_number', 'like', "%{$s}%"))
                  ->orWhere('complaint', 'like', "%{$s}%");
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return $query->limit(60)->get();
    }

    public function getCustomersProperty(): Collection
    {
        return Customer::orderBy('name')->get(['id', 'name', 'phone']);
    }

    public function getVehiclesProperty(): Collection
    {
        if ($this->customerId === '') {
            return collect();
        }

        return Vehicle::where('customer_id', $this->customerId)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'brand', 'type']);
    }

    public function getMechanicsProperty(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function getStatusesProperty(): array
    {
        return [
            WorkOrderStatus::Pending->value,
            WorkOrderStatus::InProgress->value,
            WorkOrderStatus::Completed->value,
            WorkOrderStatus::Paid->value,
        ];
    }

    // ─── Actions ───────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'customerId', 'vehicleId', 'odometer', 'complaint', 'mechanicId']);
        $this->resetValidation();
        $this->status    = WorkOrderStatus::Pending->value;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $wo = WorkOrder::findOrFail($id);
        $this->editingId  = $wo->id;
        $this->customerId = (string) ($wo->customer_id ?? '');
        $this->vehicleId  = (string) ($wo->vehicle_id ?? '');
        $this->status     = $wo->status instanceof WorkOrderStatus ? $wo->status->value : (string) $wo->status;
        $this->odometer   = (string) ($wo->odometer ?? '');
        $this->complaint  = $wo->complaint ?? '';
        $this->mechanicId = (string) ($wo->assigned_mechanic_id ?? '');
        $this->resetValidation();
        $this->isEditing  = true;
        $this->showModal  = true;
    }

    public function saveWorkOrder(): void
    {
        $this->validate();

        $branchId = Branch::first()?->id;

        $data = [
            'branch_id'            => $branchId,
            'customer_id'          => $this->customerId,
            'vehicle_id'           => $this->vehicleId,
            'status'               => $this->status,
            'odometer'             => $this->odometer !== '' ? (int) $this->odometer : null,
            'complaint'            => $this->complaint,
            'assigned_mechanic_id' => $this->mechanicId !== '' ? $this->mechanicId : null,
        ];

        if ($this->isEditing && $this->editingId !== null) {
            WorkOrder::findOrFail($this->editingId)->update($data);
            $msg = 'Work Order berhasil diperbarui.';
        } else {
            WorkOrder::create($data);
            $msg = 'Work Order baru berhasil dibuat.';
        }

        $this->showModal = false;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function deleteWorkOrder(string $id): void
    {
        WorkOrder::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Work Order berhasil dihapus.');
    }

    public function updatedCustomerId(): void
    {
        $this->vehicleId = '';
    }

    public function updatedSearch(): void
    {
        // reactive
    }
}
