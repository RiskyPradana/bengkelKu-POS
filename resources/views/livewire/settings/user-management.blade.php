<div class="p-4 space-y-5 md:p-6">

    {{-- Judul --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen User</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Kelola akun karyawan, atur role, dan reset password.</p>
        </div>

        <button wire:click="openCreate"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah User
        </button>
    </div>

    {{-- Notifikasi --}}
    @if (session('sukses'))
        <div class="flex items-start gap-3 p-4 text-sm border rounded-lg bg-emerald-50 border-emerald-200 text-emerald-800">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if (session('gagal'))
        <div class="flex items-start gap-3 p-4 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('gagal') }}</span>
        </div>
    @endif

    @unless ($hasRoleColumn)
        <div class="p-4 text-sm border rounded-lg bg-amber-50 border-amber-200 text-amber-900">
            <b>Kolom role belum tersedia.</b>
            Jalankan <code class="px-1.5 py-0.5 bg-amber-100 rounded">php artisan migrate</code>
            supaya pengaturan role bisa dipakai.
        </div>
    @endunless

    {{-- Ringkasan per role --}}
    @if (count($summary) > 0)
        <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
            @foreach ($roleList as $key => $meta)
                @php $count = $summary[$key] ?? 0; @endphp
                <button wire:click="$set('filterRole', '{{ $filterRole === $key ? '' : $key }}')"
                        @class([
                            'p-3 text-left bg-white dark:bg-slate-900 border rounded-xl transition-all',
                            'border-blue-500 ring-2 ring-blue-100' => $filterRole === $key,
                            'border-gray-200 dark:border-slate-800 hover:border-gray-300' => $filterRole !== $key,
                        ])>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $count }}</p>
                    <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-slate-400">{{ $meta['label'] }}</p>
                </button>
            @endforeach
        </div>
    @endif

    {{-- Filter --}}
    <div class="flex flex-col gap-3 p-4 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl sm:flex-row">
        <div class="relative flex-1">
            <svg class="absolute w-5 h-5 text-gray-400 dark:text-slate-500 left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" wire:model.live.debounce.400ms="search"
                   placeholder="Cari nama, email, atau nomor WA..."
                   class="w-full py-2 pl-10 pr-3 text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
        </div>

        <select wire:model.live="filterRole" class="text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500 sm:w-48">
            <option value="">Semua Role</option>
            @foreach ($roleList as $key => $meta)
                <option value="{{ $key }}">{{ $meta['label'] }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" class="text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500 sm:w-40">
            <option value="">Semua Status</option>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select>
    </div>

    {{-- Tabel user --}}
    <div class="overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-800">
                    <tr class="text-xs tracking-wider text-left text-gray-500 dark:text-slate-400 uppercase">
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold">Role</th>
                        <th class="px-4 py-3 font-semibold">Kontak</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse ($users as $u)
                        @php
                            $meta      = $roleList[$u->role ?? ''] ?? null;
                            $color     = $meta['color'] ?? 'gray';
                            $isMe      = (string) $u->id === (string) auth()->id();
                            $aktif     = ! isset($u->is_active) || $u->is_active;
                            $initials  = collect(explode(' ', (string) $u->name))
                                            ->take(2)
                                            ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
                                            ->implode('');
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 {{ $aktif ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-9 h-9 text-xs font-bold text-white rounded-full shrink-0 bg-{{ $color }}-500">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $u->name }}
                                            @if ($isMe)
                                                <span class="ml-1 px-1.5 py-0.5 text-xs font-medium text-blue-700 bg-blue-50 rounded">Anda</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400 truncate">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-{{ $color }}-50 text-{{ $color }}-700">
                                    {{ $meta['label'] ?? ($u->role ?: '-') }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">
                                @if (! empty($u->phone))
                                    @php $waUrl = 'https://wa.me/' . $u->phone; @endphp
                                    <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1 text-emerald-600 hover:underline">
                                        {{ $u->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400 dark:text-slate-500">Belum diisi</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if ($aktif)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEdit('{{ $u->id }}')"
                                            class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800"
                                            title="Ubah data">Ubah</button>

                                    <button wire:click="resetPassword('{{ $u->id }}')"
                                            wire:confirm="Reset password {{ $u->name }}? Password baru akan ditampilkan sekali saja."
                                            class="px-2.5 py-1.5 text-xs font-medium text-amber-700 rounded-md hover:bg-amber-50"
                                            title="Reset password">Reset PW</button>

                                    @unless ($isMe)
                                        <button wire:click="toggleActive('{{ $u->id }}')"
                                                class="px-2.5 py-1.5 text-xs font-medium rounded-md {{ $aktif ? 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                            {{ $aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>

                                        <button wire:click="deleteUser('{{ $u->id }}')"
                                                wire:confirm="Hapus {{ $u->name }}? Kalau user pernah dipakai di transaksi, akun hanya akan dinonaktifkan."
                                                class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">
                                            Hapus
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <p class="text-sm text-gray-500 dark:text-slate-400">
                                    @if ($search !== '' || $filterRole !== '' || $filterStatus !== '')
                                        Tidak ada user yang cocok dengan filter.
                                    @else
                                        Belum ada user. Klik "Tambah User" untuk mulai.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-800">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- Penjelasan role --}}
    <div class="p-4 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Penjelasan Role</h3>
        <div class="space-y-2.5">
            @foreach ($roleList as $key => $meta)
                <div class="flex items-start gap-3 text-sm">
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full shrink-0 bg-{{ $meta['color'] }}-50 text-{{ $meta['color'] }}-700">
                        {{ $meta['label'] }}
                    </span>
                    <span class="text-gray-600 dark:text-slate-300">{{ $meta['description'] }}</span>
                </div>
            @endforeach
        </div>

        @if (\Illuminate\Support\Facades\Route::has('settings.roles'))
            <p class="mt-3 text-xs text-gray-400 dark:text-slate-500">
                Role & hak akses tiap halaman bisa diatur di menu
                <a href="{{ route('settings.roles') }}" class="font-medium text-blue-600 hover:underline">Role & Hak Akses</a>.
            </p>
        @else
            <p class="mt-3 text-xs text-gray-400 dark:text-slate-500">
                Hak akses tiap halaman bisa diubah di file <code>config/roles.php</code>.
            </p>
        @endif
    </div>

    {{-- Modal form --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 bg-black/50 sm:items-center sm:p-4"
             wire:key="modal-user">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 shadow-xl rounded-t-2xl sm:rounded-2xl max-h-[92vh] overflow-y-auto">

                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $editingId ? 'Ubah Data User' : 'Tambah User Baru' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="p-1 text-gray-400 dark:text-slate-500 rounded hover:bg-gray-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="px-5 py-4 space-y-4">

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Nama Lengkap</label>
                        <input type="text" wire:model="name" placeholder="contoh: Budi Santoso"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Email <span class="text-gray-400 dark:text-slate-500">(dipakai untuk login)</span></label>
                        <input type="email" wire:model="email" placeholder="budi@bengkel.com"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-slate-200">Role</label>
                        <div class="space-y-2">
                            @foreach ($roleList as $key => $meta)
                                <label @class([
                                    'flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition-colors',
                                    'border-blue-500 bg-blue-50' => $role === $key,
                                    'border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800/50' => $role !== $key,
                                ])>
                                    <input type="radio" wire:model.live="role" value="{{ $key }}"
                                           class="mt-0.5 text-blue-600 border-gray-300 dark:border-slate-700 focus:ring-blue-500">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $meta['label'] }}</span>
                                        <span class="block mt-0.5 text-xs text-gray-500 dark:text-slate-400">{{ $meta['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Nomor WhatsApp <span class="text-gray-400 dark:text-slate-500">(opsional)</span></label>
                        <input type="text" wire:model="phone" placeholder="081234567890"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-400 dark:text-slate-500">Otomatis diubah ke format 62 saat disimpan.</p>
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if (count($branches) > 0)
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Cabang</label>
                            <select wire:model="branchId" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Cabang</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($role === 'mekanik')
                        <div class="space-y-4 p-3 border border-amber-100 rounded-lg bg-amber-50/50">
                            <p class="text-xs font-semibold text-amber-700">Pengaturan Profit Mekanik</p>
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Rate Komisi Khusus (%) <span class="text-gray-400 dark:text-slate-500">(opsional)</span></label>
                                <input type="number" step="0.5" min="0" max="100" wire:model="commissionRate"
                                       placeholder="kosongkan untuk pakai default {{ config('whatsapp.commission_rate', env('COMMISSION_RATE', 10)) }}%"
                                       class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-400">Profit 1: dipakai untuk komisi tiap kendaraan masuk (WO selesai dibayar).</p>
                                @error('commissionRate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Target KPI / Bulan</label>
                                    <input type="number" min="0" step="1" wire:model="monthlyTarget"
                                           placeholder="contoh: 40"
                                           class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                    @error('monthlyTarget') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Bonus KPI (Rp)</label>
                                    <input type="number" min="0" step="1000" wire:model="kpiBonusAmount"
                                           placeholder="contoh: 200000"
                                           class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                    @error('kpiBonusAmount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <p class="text-xs text-gray-400">Profit 2: bonus cair penuh jika jumlah WO selesai bulan berjalan mencapai target di atas.</p>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-gray-100 dark:border-slate-800">
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">
                            Password
                            @if ($editingId)
                                <span class="text-gray-400 dark:text-slate-500">(kosongkan kalau tidak diubah)</span>
                            @endif
                        </label>
                        <input type="password" wire:model="password" placeholder="minimal 8 karakter"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Ulangi Password</label>
                        <input type="password" wire:model="passwordConfirmation"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <label class="flex items-center gap-2.5 pt-1">
                        <input type="checkbox" wire:model="isActive"
                               class="text-blue-600 border-gray-300 dark:border-slate-700 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-slate-200">Akun aktif (bisa login)</span>
                    </label>

                    <div class="flex gap-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Simpan Perubahan' : 'Tambah User' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
