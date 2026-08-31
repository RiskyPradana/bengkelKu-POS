<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    #[Url(as: 'cari', except: '')]
    public string $search = '';

    #[Url(as: 'role', except: '')]
    public string $filterRole = '';

    #[Url(as: 'status', except: '')]
    public string $filterStatus = '';

    public bool $showModal = false;

    public ?string $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $role = 'kasir';
    public string $phone = '';
    public ?string $branchId = null;
    public bool $isActive = true;
    public string $password = '';
    public string $passwordConfirmation = '';
    public ?string $commissionRate = null;

    public function mount(): void
    {
        $this->role = (string) config('roles.default', 'kasir');
    }

    protected function rules(): array
    {
        $roleKeys = array_keys((array) config('roles.list', []));

        return [
            'name'  => ['required', 'string', 'min:3', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'role'           => ['required', Rule::in($roleKeys)],
            'phone'          => ['nullable', 'string', 'max:25'],
            'isActive'       => ['boolean'],
            'commissionRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'password'       => $this->editingId
                ? ['nullable', Password::min(8)]
                : ['required', Password::min(8)],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name'           => 'nama',
            'email'          => 'email',
            'role'           => 'role',
            'phone'          => 'nomor WhatsApp',
            'password'       => 'password',
            'commissionRate' => 'rate komisi',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $user = User::findOrFail($id);

        $this->editingId = (string) $user->id;
        $this->name      = (string) $user->name;
        $this->email     = (string) $user->email;
        $this->role      = (string) ($user->role ?? 'kasir');
        $this->phone     = (string) ($user->phone ?? '');
        $this->branchId  = $user->branch_id ? (string) $user->branch_id : null;
        $this->isActive  = (bool) ($user->is_active ?? true);
        $this->commissionRate = isset($user->commission_rate) && $user->commission_rate !== null
            ? (string) $user->commission_rate
            : null;
        $this->password = '';
        $this->passwordConfirmation = '';

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->password !== '' && $this->password !== $this->passwordConfirmation) {
            $this->addError('passwordConfirmation', 'Ulangi password tidak sama.');

            return;
        }

        $data = [
            'name'  => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'role'  => $this->role,
        ];

        if (Schema::hasColumn('users', 'phone')) {
            $data['phone'] = $this->phone !== '' ? $this->normalizePhone($this->phone) : null;
        }

        if (Schema::hasColumn('users', 'branch_id')) {
            $data['branch_id'] = $this->branchId ?: null;
        }

        if (Schema::hasColumn('users', 'is_active')) {
            $data['is_active'] = $this->isActive;
        }

        if (Schema::hasColumn('users', 'commission_rate')) {
            $data['commission_rate'] = ($this->commissionRate !== null && $this->commissionRate !== '')
                ? (float) $this->commissionRate
                : null;
        }

        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);

            if ($this->isLastOwner($user) && $this->role !== 'owner') {
                $this->addError('role', 'Ini satu-satunya owner. Angkat owner lain dulu sebelum mengubah role ini.');

                return;
            }

            $user->update($data);
            session()->flash('sukses', 'Data ' . $user->name . ' berhasil diperbarui.');
        } else {
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $data['email_verified_at'] = now();
            }

            $user = User::create($data);
            session()->flash('sukses', 'User ' . $user->name . ' berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(string $id): void
    {
        if (! Schema::hasColumn('users', 'is_active')) {
            session()->flash('gagal', 'Kolom is_active belum ada. Jalankan php artisan migrate.');

            return;
        }

        $user = User::findOrFail($id);

        if ((string) $user->id === (string) auth()->id()) {
            session()->flash('gagal', 'Tidak bisa menonaktifkan akun sendiri.');

            return;
        }

        if ($this->isLastOwner($user) && $user->is_active) {
            session()->flash('gagal', 'Ini satu-satunya owner aktif. Tidak bisa dinonaktifkan.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);

        session()->flash('sukses', $user->name . ' sekarang ' . ($user->is_active ? 'AKTIF' : 'NONAKTIF') . '.');
    }

    public function resetPassword(string $id): void
    {
        $user = User::findOrFail($id);

        $temp = 'bkl' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);

        $user->update(['password' => Hash::make($temp)]);

        session()->flash(
            'sukses',
            'Password ' . $user->name . ' direset menjadi: ' . $temp . ' (catat sekarang, tidak ditampilkan lagi)'
        );
    }

    public function deleteUser(string $id): void
    {
        $user = User::findOrFail($id);

        if ((string) $user->id === (string) auth()->id()) {
            session()->flash('gagal', 'Tidak bisa menghapus akun sendiri.');

            return;
        }

        if ($this->isLastOwner($user)) {
            session()->flash('gagal', 'Ini satu-satunya owner. Tidak bisa dihapus.');

            return;
        }

        $name = (string) $user->name;

        if ($this->hasTransactions($id) && Schema::hasColumn('users', 'is_active')) {
            $user->update(['is_active' => false]);
            session()->flash('sukses', $name . ' punya riwayat transaksi, jadi dinonaktifkan (bukan dihapus) agar data lama tetap utuh.');

            return;
        }

        $user->delete();
        session()->flash('sukses', 'User ' . $name . ' berhasil dihapus.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name      = '';
        $this->email     = '';
        $this->role      = (string) config('roles.default', 'kasir');
        $this->phone     = '';
        $this->branchId  = null;
        $this->isActive  = true;
        $this->password  = '';
        $this->passwordConfirmation = '';
        $this->commissionRate = null;

        $this->resetValidation();
    }

    private function isLastOwner(User $user): bool
    {
        if (($user->role ?? '') !== 'owner') {
            return false;
        }

        return User::where('role', 'owner')->count() <= 1;
    }

    private function hasTransactions(string $userId): bool
    {
        $checks = [
            ['table' => 'work_orders', 'column' => 'mechanic_id'],
            ['table' => 'invoices', 'column' => 'user_id'],
            ['table' => 'mechanic_commissions', 'column' => 'mechanic_id'],
        ];

        foreach ($checks as $check) {
            try {
                if (! Schema::hasTable($check['table'])) {
                    continue;
                }

                if (! Schema::hasColumn($check['table'], $check['column'])) {
                    continue;
                }

                if (DB::table($check['table'])->where($check['column'], $userId)->exists()) {
                    return true;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return false;
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    public function render()
    {
        $hasRoleColumn = Schema::hasColumn('users', 'role');

        $query = User::query();

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)->orWhere('email', 'like', $term);

                if (Schema::hasColumn('users', 'phone')) {
                    $q->orWhere('phone', 'like', $term);
                }
            });
        }

        if ($this->filterRole !== '' && $hasRoleColumn) {
            $query->where('role', $this->filterRole);
        }

        if ($this->filterStatus !== '' && Schema::hasColumn('users', 'is_active')) {
            $query->where('is_active', $this->filterStatus === 'aktif');
        }

        $users = $query->orderBy('name')->paginate(15);

        $summary = [];

        if ($hasRoleColumn) {
            $summary = User::query()
                ->selectRaw('role, COUNT(*) as jumlah')
                ->groupBy('role')
                ->pluck('jumlah', 'role')
                ->all();
        }

        $branches = [];

        try {
            if (Schema::hasTable('branches')) {
                $branches = DB::table('branches')->select('id', 'name')->orderBy('name')->get()->all();
            }
        } catch (\Throwable $e) {
            $branches = [];
        }

        return view('livewire.settings.user-management', [
            'users'         => $users,
            'summary'       => $summary,
            'branches'      => $branches,
            'roleList'      => (array) config('roles.list', []),
            'hasRoleColumn' => $hasRoleColumn,
        ])->layout('layouts.app', ['title' => 'Manajemen User']);
    }
}
