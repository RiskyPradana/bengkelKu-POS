<?php

use App\Livewire\Auth\Login;
use App\Livewire\Catalog\Index as CatalogIndex;
use App\Livewire\Commission\Index as CommissionIndex;
use App\Livewire\CRM\Reminders;
use App\Livewire\Customer\Index as CustomerIndex;
use App\Livewire\Dashboard;
use App\Livewire\Analytics\OwnerDashboard;
use App\Livewire\Settings\UserManagement;
use App\Livewire\Settings\BranchManagement;
use App\Livewire\Settings\RoleSettings;
use App\Livewire\Inventory\Index as InventoryIndex;
use App\Livewire\MobileMechanic\Home as MobileHome;
use App\Livewire\MobileMechanic\Scanner;
use App\Livewire\MobileMechanic\WorkOrders as MobileWorkOrders;
use App\Livewire\Pos\Cashier;
use App\Livewire\Purchasing\Index as PurchasingIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\WorkOrder\Index as WorkOrderIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| BengkelOS Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('dashboard'));

// ── Auth (Guest only) ───────────────────────────────────────────

Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

// ── Authenticated Pages (Desktop) ──────────────────────────────
// Sesi 11: middleware 'role.access' ditambahkan supaya hak akses role
// (diatur di /pengaturan/role) juga ditegakkan di level route, bukan cuma
// menyembunyikan menu di sidebar.

Route::middleware(['auth', 'role.access'])->group(function (): void {

    // Ringkasan
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Fase 3: Dashboard Analitik Owner (grafik omzet, sparepart terlaris, performa mekanik)
    Route::get('/analitik', OwnerDashboard::class)->name('analytics');

    // Pengaturan: manajemen user, cabang & role
    Route::get('/pengaturan/user',   UserManagement::class)->name('settings.users');
    Route::get('/pengaturan/cabang', BranchManagement::class)->name('settings.branches');
    Route::get('/pengaturan/role',   RoleSettings::class)->name('settings.roles');

    // Operasional
    Route::get('/work-orders', WorkOrderIndex::class)->name('work-orders');
    Route::get('/kasir',       Cashier::class)->name('pos.cashier');

    // Data Master
    Route::get('/customers', CustomerIndex::class)->name('customers');
    Route::get('/catalog',   CatalogIndex::class)->name('catalog');

    // Modul 4: Stok & Inventori Multi-Cabang
    Route::get('/inventory', InventoryIndex::class)->name('inventory');

    // Modul 6: CRM & Pengingat WhatsApp
    Route::get('/crm/reminders', Reminders::class)->name('crm.reminders');

    // Modul 5: Komisi Mekanik
    Route::get('/commission', CommissionIndex::class)->name('commission');

    // Pembelian & Supplier
    Route::get('/purchasing', PurchasingIndex::class)->name('purchasing');

    // Laporan
    Route::get('/reports', ReportsIndex::class)->name('reports');
});

// ── Mobile Mekanik (PWA) ─────────────────────────────────────
// Modul 3: Mobile App untuk Mekanik
// Sesi 11: middleware 'role.access' juga dipasang di sini.

Route::middleware(['auth', 'role.access'])->prefix('mobile')->name('mobile.')->group(function (): void {
    Route::get('/',        MobileHome::class)->name('home');
    Route::get('/scanner', Scanner::class)->name('scanner');
    Route::get('/wo',      MobileWorkOrders::class)->name('wo');
});
