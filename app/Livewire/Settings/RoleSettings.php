<?php

namespace App\Livewire\Settings;

use App\Domains\MasterData\Models\RoleSetting;
use App\Domains\MasterData\Services\RoleRegistry;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RoleSettings extends Component
{
    public bool $showModal = false;

    public ?string $editingKey = null;

    public string $key = '';
    public string $label = '';
    public string $description = '';
    public string $color = 'blue';
    public int $level = 50;

    /** @var array<int, string> */
    public array $access = [];

    public function mount(): void
    {
        $this->ensureSeeded();
    }

    protected function rules(): array
    {
        return [
            'key'         => [
                'required', 'string', 'max:30', 'alpha_dash',
                Rule::unique('role_settings', 'key')->ignore($this->editingKey, 'key'),
            ],
            'label'       => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'color'       => ['required', 'string', 'max:20'],
            'level'       => ['required', 'integer', 'min:0', 'max:100'],
            'access'      => ['array'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'key'   => 'kode role',
            'label' => 'nama role',
            'level' => 'level',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $key): void
    {
        $role = RoleSetting::findOrFail($key);

        $this->editingKey  = $role->key;
        $this->key         = $role->key;
        $this->label       = $role->label;
        $this->description = (string) $role->description;
        $this->color       = $role->color;
        $this->level       = $role->level;
        $this->access      = (array) ($role->access ?? []);

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'key'         => $this->key,
            'label'       => $this->label,
            'description' => $this->description !== '' ? $this->description : null,
            'color'       => $this->color,
            'level'       => $this->level,
            'access'      => array_values($this->access),
        ];

        if ($this->editingKey) {
            $role = RoleSetting::findOrFail($this->editingKey);

            if ($role->is_system && $this->key !== $role->key) {
                $this->addError('key', 'Kode role bawaan sistem ("' . $role->key . '") tidak bisa diubah.');

                return;
            }

            $role->update($data);
            session()->flash('sukses', 'Role ' . $role->label . ' berhasil diperbarui.');
        } else {
            $data['is_default'] = false;
            $data['is_system']  = false;

            RoleSetting::create($data);
            session()->flash('sukses', 'Role ' . $this->label . ' berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function makeDefault(string $key): void
    {
        RoleSetting::query()->update(['is_default' => false]);
        RoleSetting::whereKey($key)->update(['is_default' => true]);

        session()->flash('sukses', 'Role default untuk user baru sekarang: ' . $key);
    }

    public function deleteRole(string $key): void
    {
        $role = RoleSetting::findOrFail($key);

        if ($role->is_system) {
            session()->flash('gagal', 'Role bawaan sistem ("' . $role->label . '") tidak bisa dihapus.');

            return;
        }

        $userCount = 0;

        try {
            if (Schema::hasColumn('users', 'role')) {
                $userCount = User::where('role', $key)->count();
            }
        } catch (\Throwable $e) {
            $userCount = 0;
        }

        if ($userCount > 0) {
            session()->flash('gagal', 'Masih ada ' . $userCount . ' user dengan role ini. Pindahkan dulu sebelum menghapus.');

            return;
        }

        $role->delete();
        session()->flash('sukses', 'Role ' . $role->label . ' berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingKey  = null;
        $this->key         = '';
        $this->label       = '';
        $this->description = '';
        $this->color       = 'blue';
        $this->level       = 50;
        $this->access      = [];

        $this->resetValidation();
    }

    /**
     * Jaga-jaga: kalau migration belum jalan / tabel masih kosong, isi dari
     * config/roles.php supaya halaman ini tidak kosong melompong.
     */
    private function ensureSeeded(): void
    {
        if (! RoleRegistry::available()) {
            return;
        }

        if (RoleSetting::query()->exists()) {
            return;
        }

        $roleList   = (array) config('roles.list', []);
        $accessMap  = (array) config('roles.access', []);
        $defaultKey = (string) config('roles.default', 'kasir');

        foreach ($roleList as $key => $meta) {
            $roleAccess = [];

            foreach ($accessMap as $route => $roles) {
                if (in_array($key, (array) $roles, true)) {
                    $roleAccess[] = $route;
                }
            }

            RoleSetting::create([
                'key'         => $key,
                'label'       => $meta['label'] ?? $key,
                'description' => $meta['description'] ?? null,
                'color'       => $meta['color'] ?? 'gray',
                'level'       => $meta['level'] ?? 0,
                'access'      => $roleAccess,
                'is_default'  => $key === $defaultKey,
                'is_system'   => $key === 'owner',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.settings.role-settings', [
            'roles'            => RoleSetting::query()->orderByDesc('level')->get(),
            'manageableRoutes' => RoleRegistry::manageableRoutes(),
        ])->layout('layouts.app', ['title' => 'Role & Hak Akses']);
    }
}
