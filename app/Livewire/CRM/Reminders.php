<?php

namespace App\Livewire\CRM;

use App\Domains\CRM\Models\ServiceReminder;
use App\Domains\CRM\Models\WhatsappLog;
use App\Domains\CRM\Services\WhatsAppService;
use App\Domains\CustomerVehicle\Models\Customer;
use App\Domains\CustomerVehicle\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Reminders extends Component
{
    use WithPagination;

    public string $tab    = 'reminders'; // reminders | logs
    public string $search = '';
    public string $filterStatus = '';

    // Form
    public bool    $showModal    = false;
    public bool    $editingMode  = false;
    public ?string $reminderId   = null;
    public string  $fVehicleId   = '';
    public string  $fTrigger     = 'interval_days';
    public string  $fIntervalDay = '90';
    public string  $fMileage     = '5000';
    public string  $fLastDate    = '';
    public string  $fLastMileage = '';

    #[Computed]
    public function reminders()
    {
        $q = ServiceReminder::with('vehicle', 'customer')->latest();
        if ($this->search) {
            $s = $this->search;
            $q->whereHas('vehicle', fn($v) => $v->where('plate_number', 'like', "%{$s}%"))
              ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$s}%"));
        }
        return $q->paginate(20);
    }

    #[Computed]
    public function dueReminders(): Collection
    {
        return ServiceReminder::with('vehicle', 'customer')
            ->where('is_active', true)
            ->where('next_reminder_date', '<=', now()->toDateString())
            ->get();
    }

    #[Computed]
    public function whatsappLogs()
    {
        return WhatsappLog::latest()->paginate(20);
    }

    #[Computed]
    public function vehicles(): Collection
    {
        return Vehicle::with('customer')->orderBy('plate_number')->get();
    }

    public function render(): View
    {
        return view('livewire.crm.reminders')
            ->with([
                'reminders'   => $this->reminders,
                'waLogs'      => $this->whatsappLogs,
                'allVehicles' => $this->vehicles,
            ])
            ->layout('layouts.admin', [
                'title'     => 'CRM & Reminder — BengkelOS',
                'pageTitle' => 'CRM & Reminder',
                'pageSub'   => 'Pengingat servis dan log WhatsApp',
            ]);
    }

    public function switchTab(string $tab): void { $this->tab = $tab; }

    public function openCreate(): void
    {
        $this->reset(['reminderId', 'fVehicleId', 'fLastDate', 'fLastMileage']);
        $this->fTrigger     = 'interval_days';
        $this->fIntervalDay = '90';
        $this->fMileage     = '5000';
        $this->editingMode  = false;
        $this->showModal    = true;
    }

    public function saveReminder(): void
    {
        $this->validate([
            'fVehicleId' => 'required',
            'fTrigger'   => 'required|in:interval_days,mileage',
        ], ['fVehicleId.required' => 'Pilih kendaraan.']);

        $vehicle = Vehicle::findOrFail($this->fVehicleId);

        $nextDate = $this->fTrigger === 'interval_days' && $this->fLastDate
            ? now()->parse($this->fLastDate)->addDays((int) $this->fIntervalDay)->toDateString()
            : null;

        $data = [
            'vehicle_id'           => $this->fVehicleId,
            'customer_id'          => $vehicle->customer_id,
            'trigger_type'         => $this->fTrigger,
            'interval_days'        => $this->fTrigger === 'interval_days' ? (int) $this->fIntervalDay : null,
            'mileage_interval'     => $this->fTrigger === 'mileage' ? (int) $this->fMileage : null,
            'last_service_date'    => $this->fLastDate    ?: null,
            'last_service_mileage' => $this->fLastMileage ?: null,
            'next_reminder_date'   => $nextDate,
            'is_active'            => true,
        ];

        if ($this->editingMode && $this->reminderId) {
            ServiceReminder::findOrFail($this->reminderId)->update($data);
        } else {
            ServiceReminder::create($data);
        }

        $this->showModal = false;
        $this->dispatch('notify', type: 'success', message: 'Reminder berhasil disimpan.');
    }

    public function sendReminder(string $reminderId): void
    {
        $reminder = ServiceReminder::with('vehicle.customer')->findOrFail($reminderId);
        $customer = $reminder->customer;

        if (!$customer?->phone) {
            $this->dispatch('notify', type: 'error', message: 'Nomor HP pelanggan belum diisi.');
            return;
        }

        app(WhatsAppService::class)->sendServiceReminder(
            $customer->phone,
            $customer->name,
            $reminder->vehicle->plate_number,
            $reminder->next_reminder_date?->format('d/m/Y') ?? '-',
            $customer->id,
        );

        $this->dispatch('notify', type: 'success', message: 'Pesan WhatsApp dikirim ke ' . $customer->phone);
    }

    public function deleteReminder(string $id): void
    {
        ServiceReminder::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Reminder dihapus.');
    }
}
