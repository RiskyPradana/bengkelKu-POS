<?php

namespace App\Livewire\Customer;

use App\Domains\CustomerVehicle\Models\Customer;
use App\Domains\CustomerVehicle\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    // Customer Modal
    public bool    $showModal  = false;
    public bool    $isEditing  = false;
    public ?string $editingId  = null;
    public string  $custName   = '';
    public string  $custPhone  = '';
    public string  $custEmail  = '';
    public string  $custAddress = '';

    // Vehicle Modal
    public bool    $showVehicleModal    = false;
    public bool    $editingVehicle      = false;
    public ?string $vehicleId           = null;
    public ?string $vehicleCustomerId   = null;
    public string  $vehicleCustomerName = '';
    public string  $vPlate   = '';
    public string  $vBrand   = '';
    public string  $vType    = '';
    public string  $vYear    = '';
    public string  $vMileage = '';

    // Vehicle list
    public ?string $vehicleListCustomerId   = null;
    public string  $vehicleListCustomerName = '';
    public bool    $showVehicleList         = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function render(): View
    {
        return view('livewire.customer.index')
            ->layout('layouts.admin', [
                'title'     => 'Pelanggan — BengkelOS',
                'pageTitle' => 'Pelanggan',
                'pageSub'   => 'Data pelanggan dan kendaraan yang terdaftar',
            ]);
    }

    public function getCustomersProperty(): Collection
    {
        $query = Customer::withCount(['vehicles', 'workOrders'])
            ->latest();

        if ($this->search !== '') {
            $s = $this->search;
            $query->where(function ($q) use ($s): void {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhereHas('vehicles', fn ($q2) => $q2->where('plate_number', 'like', "%{$s}%"));
            });
        }

        return $query->limit(60)->get();
    }

    public function getVehicleListProperty(): Collection
    {
        if (! $this->vehicleListCustomerId) {
            return collect();
        }

        return Vehicle::where('customer_id', $this->vehicleListCustomerId)->get();
    }

    // ─── Customer CRUD ─────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'custName', 'custPhone', 'custEmail', 'custAddress']);
        $this->resetValidation();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $c = Customer::findOrFail($id);
        $this->editingId   = $c->id;
        $this->custName    = $c->name;
        $this->custPhone   = $c->phone   ?? '';
        $this->custEmail   = $c->email   ?? '';
        $this->custAddress = $c->address ?? '';
        $this->resetValidation();
        $this->isEditing   = true;
        $this->showModal   = true;
    }

    public function saveCustomer(): void
    {
        $this->validate([
            'custName'  => 'required|min:2',
            'custPhone' => 'nullable|string|max:20',
            'custEmail' => 'nullable|email|max:100',
        ], [
            'custName.required' => 'Nama pelanggan wajib diisi.',
            'custName.min'      => 'Nama minimal 2 karakter.',
            'custEmail.email'   => 'Format email tidak valid.',
        ]);

        $data = [
            'name'    => $this->custName,
            'phone'   => $this->custPhone   ?: null,
            'email'   => $this->custEmail   ?: null,
            'address' => $this->custAddress ?: null,
        ];

        if ($this->isEditing && $this->editingId) {
            Customer::findOrFail($this->editingId)->update($data);
            $msg = 'Data pelanggan berhasil diperbarui.';
        } else {
            Customer::create($data);
            $msg = 'Pelanggan baru berhasil ditambahkan.';
        }

        $this->showModal = false;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function deleteCustomer(string $id): void
    {
        Customer::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Pelanggan berhasil dihapus.');
    }

    // ─── Vehicle Modal ─────────────────────────────────────────

    public function openVehicleList(string $customerId, string $customerName): void
    {
        $this->vehicleListCustomerId   = $customerId;
        $this->vehicleListCustomerName = $customerName;
        $this->showVehicleList         = true;
    }

    public function openAddVehicle(string $customerId, string $customerName): void
    {
        $this->reset(['vehicleId', 'vPlate', 'vBrand', 'vType', 'vYear', 'vMileage']);
        $this->resetValidation();
        $this->vehicleCustomerId   = $customerId;
        $this->vehicleCustomerName = $customerName;
        $this->editingVehicle      = false;
        $this->showVehicleModal    = true;
    }

    public function openEditVehicle(string $vehicleId): void
    {
        $v = Vehicle::findOrFail($vehicleId);
        $this->vehicleId           = $v->id;
        $this->vehicleCustomerId   = (string) $v->customer_id;
        $this->vehicleCustomerName = $v->customer?->name ?? '';
        $this->vPlate              = $v->plate_number;
        $this->vBrand              = $v->brand   ?? '';
        $this->vType               = $v->type    ?? '';
        $this->vYear               = (string) ($v->year ?? '');
        $this->vMileage            = (string) ($v->last_mileage ?? '');
        $this->resetValidation();
        $this->editingVehicle   = true;
        $this->showVehicleModal = true;
    }

    public function saveVehicle(): void
    {
        $this->validate([
            'vPlate' => 'required|min:3|unique:vehicles,plate_number' . ($this->editingVehicle && $this->vehicleId ? ',' . $this->vehicleId : ''),
            'vBrand' => 'required|min:2',
        ], [
            'vPlate.required' => 'Plat nomor wajib diisi.',
            'vPlate.unique'   => 'Plat nomor ini sudah terdaftar di sistem.',
            'vBrand.required' => 'Merek kendaraan wajib diisi.',
        ]);

        $data = [
            'customer_id'  => $this->vehicleCustomerId,
            'plate_number' => strtoupper(trim($this->vPlate)),
            'brand'        => $this->vBrand,
            'type'         => $this->vType    ?: null,
            'year'         => $this->vYear    !== '' ? (int) $this->vYear    : null,
            'last_mileage' => $this->vMileage !== '' ? (int) $this->vMileage : null,
        ];

        if ($this->editingVehicle && $this->vehicleId) {
            Vehicle::findOrFail($this->vehicleId)->update($data);
            $msg = 'Data kendaraan berhasil diperbarui.';
        } else {
            Vehicle::create($data);
            $msg = 'Kendaraan berhasil ditambahkan.';
        }

        $this->showVehicleModal = false;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function deleteVehicle(string $id): void
    {
        Vehicle::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Kendaraan berhasil dihapus.');
    }
}
