# Chat History - BengkelKu-POS

## Sesi 1: Struktur Aplikasi & Arsitektur

### User: Buatkan struktur aplikasi BengkelOS dengan Laravel ecosystem

Membuat struktur aplikasi modular dengan stack:
- Laravel 11.x, PHP 8.2/8.3
- Livewire 3 + Alpine.js + Tailwind CSS
- Filament v3
- PostgreSQL / MySQL 8
- Spatie Laravel-Permission untuk RBAC
- PWA + HTML5 barcode scanning

Modul:
1. Master Data & Hak Akses
2. Work Order (SPK) & POS Kasir
3. Mobile Mekanik
4. Multi-Cabang & Inventaris
5. Komisi & Laporan
6. CRM & WhatsApp
7. Hybrid Offline Sync

### User: "1" → Lanjut ke Master Data & Hak Akses

Membuat:
- Laravel project scaffold dari nol
- Domain-based folder structure (`app/Domains/`)
- User model dengan `HasRoles`, `HasUuids`
- Branch, Customer, Vehicle, Product, ServiceItem models
- Filament resources untuk setiap master data
- RBAC dengan Spatie Laravel-Permission

### User: "3" → Lanjut ke Work Order & POS

Membuat:
- WorkOrder model dengan relasi (branch, customer, vehicle, mechanic, items, invoice)
- WorkOrderItem model dengan schema split (`product_id`, `service_item_id`)
- WorkOrderStatus enum (Pending, InProgress, Completed, Paid)
- WorkOrderService dengan metode CRUD dan status transitions
- WorkOrder Filament resource dengan ItemsRelationManager

---

## Sesi 2: Build Aplikasi

### User: "bisakah kamu mulai membangun aplikasinya?"

Memulai pembangunan dari struktur yang sudah direncanakan.

### User: "oke yg 1, karna masih basic ya? baru lanjut ke 2"

Membangun modul 1 (Master Data) terlebih dahulu karena masih basic.

### User: "ke lanjut"

Melanjutkan ke modul berikutnya.

---

## Sesi 3: Work Order & POS Flow

### User: "Oke lanjut ke yang aman"

Melanjutkan ke flow yang sudah aman (POS/invoice basic flow).

### User: "lanjut action/service Work Order dulu"

Membuat `WorkOrderService` dengan metode:
- `addProductItem()`, `addServiceItem()`
- `updateItemQuantity()`, `removeItem()`
- `snapshotPricing()`, `buildPriceSnapshot()`
- `transitionTo()`, `markInProgress()`, `markCompleted()`, `markPaid()`

### User: "lanjut ke item editor WorkOrder"

Membuat `ItemsRelationManager` untuk edit item SPK di Filament.

### User: "lanjut perapihan item SPK"

Merapikan item schema dengan split `product_id` dan `service_item_id`.

### User: "lanjut ke POS/invoice basic flow"

Membuat `POSService` dengan:
- `createInvoiceFromWorkOrder()` - membuat invoice dari SPK
- `recordPayment()` - mencatat pembayaran
- Invoice model dengan computed attributes (`paid_amount`, `outstanding_amount`)

### User: "lanjut action button invoice di WorkOrderResource"

Menambahkan action button invoice di Filament WorkOrder edit page.

### User: "lanjut ke payment summary"

Menambahkan payment summary section di cashier view.

---

## Sesi 4: Kasir POS UI (Livewire)

### User: "Oke lanjut, nanti saya mau tampilannya seperti ini untuk kasirnya"

Membuat tampilan kasir POS mirip mockup yang diberikan.

### User: "bagaimana cara mengirim kalau di terminal seperti ini"

Mengirim screenshot dari terminal.

### User: D:\FREELANCE\NONA\BengkelKu-POS\mockup\kasir.png

Mockup tampilan kasir POS dengan:
- Header kasir dengan info cabang
- Daftar SPK cepat
- Katalog produk & jasa
- Keranjang/invoice
- Payment summary
- Quick action buttons

### User: Membuat cashier screen statis

Membuat `resources/views/pos/cashier.blade.php` dengan Tailwind CSS statis.

---

## Sesi 5: Konversi ke Livewire

### User: Mengkonversi cashier statis ke Livewire

Membuat:
- `app/Livewire/Pos/Cashier.php` - komponen Livewire
- `resources/views/livewire/pos/cashier.blade.php` - view Livewire
- Route `/kasir` diarahkan ke komponen Livewire

---

## Sesi 6: Fix Undefined Variable Error

### User: "cek kembali, sebelumnya sudah saya npm install dan composer install, php artisan migrate, key dan config cache view clear. tapi ketika akses /kasir undefined variable selectedworkorder"

**Masalah:** View Livewire mengakses `$selectedWorkOrder` sebagai variabel Blade biasa, padahal data berasal dari computed property Livewire.

**Fix:** Mengubah semua akses di view menjadi `$this->selectedWorkOrder`, `$this->paymentSummary`, `$this->quickWorkOrders`, `$this->catalogItems`, `$this->cartLines`.

### User: "oke sudah aman, tapi belum bisa di klik2, lanjutkan tanpa test"

**Masalah:** Tombol-tombol di Livewire tidak bisa diklik.

**Fix yang dilakukan:**
1. Update `resources/views/layouts/app.blade.php` - tambah `@livewireStyles`, `@livewireScripts`, dan `csrf-token`
2. Update `app/Livewire/Pos/Cashier.php` - arahkan ke layout `layouts.app`, tambah listener refresh
3. Update `resources/js/app.js` - tambah hook Livewire
4. Bersihkan file Cashier.php dari duplicate methods

---

## Sesi 7: Daftar Link Modul

### User: "link apa saja yang sudah bisa di lihat"

Link yang sudah tersedia:
- `/` - Home
- `/dashboard` - Dashboard umum
- `/kasir` - Cashier POS
- `/admin/login` - Login Filament
- `/admin` - Dashboard Filament
- `/admin/branches` - Cabang
- `/admin/customers` - Pelanggan
- `/admin/vehicles` - Kendaraan
- `/admin/products` - Produk/Sparepart
- `/admin/services` - Jasa
- `/admin/work-orders` - SPK
- `/admin/payments` - Pembayaran

### User: "oke lanjutkan"

Melanjutkan pengembangan.

---

## Sesi 8: Rencana Lanjutan (Plan Mode)

### User: "stop dulu ya" → "Oke, stop dulu. Semua history chat masukan dalam chat.md ya"

Menghentikan sementara dan menyimpan history chat ke file ini.

---

## Sesi 9: Manajemen Cabang, Role & Hak Akses via Web

### Konteks

Setelah modul Pembelian & Supplier (Purchasing) selesai dibangun dan migration-nya siap dijalankan, user bertanya lanjut apa, sekaligus menegaskan:
1. Menu Cabang belum ada di sistem.
2. Minta agar riwayat chat & pekerjaan disimpan di file ini (`chat.md`) supaya AI di sesi berikutnya tahu apa yang sudah dikerjakan.
3. Minta semua menu dicek ulang agar tampil dengan benar.
4. Minta agar Role & Hak Akses bisa diatur lewat halaman web, tidak hanya lewat file `config/roles.php`.

### Rekap modul yang sudah dibangun sebelum sesi ini (belum tercatat di file ini)

- **Modul 3 - Mobile Mekanik (PWA):** `app/Livewire/MobileMechanic/{Home,Scanner,WorkOrders}.php`, route prefix `/mobile`, untuk mekanik memindai barcode sparepart dan mengerjakan SPK dari HP.
- **Modul 4 - Stok & Inventaris Multi-Cabang:** `app/Domains/Inventory/Services/StockService.php` (metode `adjust()` untuk stok masuk/keluar/penyesuaian), `app/Livewire/Inventory/Index.php`, route `/inventory`. Di sinilah **stok awal** dan **penyesuaian manual** dicatat.
- **Modul 5 - Komisi Mekanik:** `app/Livewire/Commission/Index.php`, route `/commission`, menghitung komisi dari SPK yang sudah dibayar.
- **Modul 6 - CRM & Pengingat WhatsApp:** `app/Livewire/CRM/Reminders.php`, route `/crm/reminders`, badge jumlah pengingat jatuh tempo di sidebar.
- **Fase 3 - Dashboard Analitik Owner:** `app/Livewire/Analytics/OwnerDashboard.php`, route `/analitik`, grafik omzet, sparepart terlaris, performa mekanik (pakai Chart.js).
- **Manajemen User:** `app/Livewire/Settings/UserManagement.php`, route `/pengaturan/user`, CRUD user + role + reset password + nonaktifkan (bukan hapus permanen kalau punya riwayat transaksi).
- **Modul Pembelian & Supplier (dibangun tepat sebelum sesi ini):** migration tabel `suppliers`, `purchase_orders`, `purchase_order_items`, `purchase_payments`; model `Supplier`, `PurchaseOrder`, `PurchaseOrderItem`, `PurchasePayment`; `app/Livewire/Purchasing/Index.php`, route `/purchasing`, badge PO belum lunas di sidebar. **Ini adalah titik "Pembelian" yang menambah stok** (goods receipt → `StockService::adjust()`).

### Pekerjaan sesi ini

1. **Role & Hak Akses via Web** (menjawab "roles seting bisa di atur lewat web"):
   - Migration tabel `role_settings` (key, label, description, color, level, access JSON, is_default, is_system) — seed otomatis dari 5 role default (owner/admin/kasir/gudang/mekanik) memakai `insertOrIgnore`.
   - Model `App\Domains\MasterData\Models\RoleSetting`.
   - Service `App\Domains\MasterData\Services\RoleRegistry` — jembatan antara `config/roles.php` (fallback) dan tabel `role_settings` (setelah migration jalan). Semua tempat yang tadinya baca `config('roles.*')` langsung sekarang baca lewat `RoleRegistry` supaya otomatis pakai data dari database begitu tabelnya ada.
   - Livewire `App\Livewire\Settings\RoleSettings` + view — CRUD role baru, ubah label/warna/level/hak akses per halaman, jadikan default, hapus role (kecuali role sistem atau role yang masih dipakai user). Route: `/pengaturan/role` (`settings.roles`), khusus **owner**.
2. **Manajemen Cabang** (menjawab "menu cabang belum ada"):
   - Livewire `App\Livewire\Settings\BranchManagement` + view — CRUD cabang (nama, kode, alamat, telepon, aktif/nonaktif). Hapus cabang terakhir yang tersisa ditolak; cabang yang sudah dipakai transaksi/stok dinonaktifkan saja, bukan dihapus permanen. Route: `/pengaturan/cabang` (`settings.branches`), untuk **owner & admin**.
3. **Sinkronisasi `RoleRegistry` ke seluruh sistem:**
   - `resources/views/components/sidebar-addons.blade.php` — pakai `RoleRegistry::access()`, tambah menu "Manajemen Cabang" dan "Role & Hak Akses" di grup Pengaturan.
   - `app/Livewire/Settings/UserManagement.php` — role default & daftar role sekarang dari `RoleRegistry`, bukan langsung dari `config()`.
   - `resources/views/components/layouts/app.blade.php` — label role di kartu profil sidebar dari `RoleRegistry::list()`.
   - `resources/views/livewire/settings/user-management.blade.php` — catatan kaki sekarang link ke halaman "Role & Hak Akses" (kalau route-nya sudah ada), bukan lagi menyuruh edit file config.
   - `config/roles.php` — ditambahkan entri akses untuk `settings.branches` dan `settings.roles`, tetap dipertahankan sebagai fallback/nilai awal.
4. **`routes/web.php`** — ditambahkan dua route baru: `settings.branches` (`/pengaturan/cabang`) dan `settings.roles` (`/pengaturan/role`).
5. **Riwayat kerja** — file `chat.md` ini diperbarui (sesi ini) agar sesi AI berikutnya punya konteks lengkap tanpa harus menjelajah ulang seluruh repo.

### Catatan penting untuk sesi berikutnya

- **Setelah pull, wajib jalankan `php artisan migrate` lagi** — ada tabel baru `role_settings`.
- Nama tabel role **bukan** `roles` (itu sudah dipakai tabel bawaan Spatie Permission yang tidak dipakai aktif) — dipakai `role_settings` supaya tidak konflik.
- Kalau tabel `role_settings` belum ada/masih kosong, `RoleRegistry` otomatis jatuh ke `config/roles.php` supaya sistem tidak error/kosong.
- Cara kerja input stok yang sudah ada di sistem:
  - **Stok awal / penyesuaian manual** → menu **Stok Multi-Cabang** (`/inventory`) → `StockService::adjust()` dengan tipe "penyesuaian".
  - **Pembelian (menambah stok dari supplier)** → menu **Pembelian & Supplier** (`/purchasing`) → saat PO diterima/goods receipt, stok bertambah otomatis lewat `StockService::adjust()` dengan tipe "pembelian".
  - Kedua jalur ini mencatat riwayat mutasi stok, bukan mengubah angka stok secara langsung.

---

## Sesi 10: Modul 7 - Hybrid Offline Sync + Perbaikan Bug Mobile Mekanik

### Konteks

User minta lanjutkan sekaligus 4 hal ("oke lanjutkan semua"): Payment capture UI kasir, Tombol cetak struk, Scan barcode & hold transaksi, dan Hybrid Offline Sync.

### Temuan penting

Setelah mengecek kode terbaru, **3 dari 4 item ternyata sudah selesai** (dikerjakan di sesi/interaksi sebelumnya yang belum tercatat di file ini):
- ✅ Payment capture UI kasir (metode, nominal, referensi pembayaran)
- ✅ Tombol Cetak Struk
- ✅ Scan Barcode & Hold Transaksi di kasir

Semua di `app/Livewire/Pos/Cashier.php` & `resources/views/livewire/pos/cashier.blade.php`.

Yang **benar-benar belum ada**: **Modul 7 - Hybrid Offline Sync**. Sudah ada rintisan (`public/sw.js`, `public/manifest.json`, `routes/pwa.php`, `routes/sync.php`) tapi masih kosong/tidak terhubung ke apa pun.

Saat menggarap Modul 7 untuk PWA Mobile Mekanik, ditemukan beberapa **bug lama yang membuat modul Mobile Mekanik (`/mobile`, `/mobile/scanner`, `/mobile/wo`) sebenarnya rusak sejak awal dibuat**, sekarang sudah diperbaiki:
1. `Home.php`, `Scanner.php`, `WorkOrders.php` membandingkan status SPK dengan string kecil `'pending'`, `'in_progress'`, `'done'` — padahal enum asli `WorkOrderStatus` memakai `'Pending'`, `'In Progress'`, `'Completed'`, `'Paid'`. Akibatnya daftar SPK di HP mekanik selalu kosong/salah, dan menyimpan status baru bisa membuat SPK tersebut error/crash saat dibuka lagi di modul lain.
2. `resources/views/livewire/mobile/wo.blade.php` memanggil `startWork({{ $wo->id }})` / `finishWork({{ $wo->id }})` **tanpa tanda kutip** — karena ID SPK berupa UUID (bukan angka), ini membuat tombol "Mulai Kerjakan"/"Selesai" selalu error saat ditekan. Sudah diberi tanda kutip.
3. `WorkOrders.php` mendeklarasikan `startWork(int $id)` / `finishWork(int $id)` padahal ID SPK adalah UUID (teks) — sudah diperbaiki jadi `string $id`, dan sekarang memakai `WorkOrderService::markInProgress()/markCompleted()` (konsisten dengan modul lain, otomatis mengikuti aturan transisi status).
4. `Scanner.php` method `addToWorkOrder()` menyimpan item SPK dengan nama kolom yang salah (`quantity`, `name_snapshot`, `cost_price_snapshot`) yang **tidak ada** di tabel `work_order_items` (kolom sebenarnya: `qty`, `name`, `snapshot`) — akibatnya tombol "Tambah ke WO" di scanner mekanik selalu gagal disimpan. Sudah diperbaiki.

### Yang dibangun (Modul 7 - Hybrid Offline Sync)

- **Alur kerja:** saat mekanik menekan "Mulai Kerjakan"/"Selesai" (Work Order) atau "Tambah ke WO" (Scanner) sambil offline, aksinya disimpan dulu di IndexedDB browser lewat `resources/js/offline-sync.js`, lalu otomatis dikirim ulang ke server via endpoint baru `/api/sync/push` begitu koneksi kembali — lewat Background Sync API (`public/sw.js`, v2) atau fallback manual saat event `online` terdeteksi (untuk browser yang belum dukung Background Sync, misalnya Safari/iOS).
- **Endpoint baru:** `app/Http/Controllers/Api/OfflineSyncController.php` + `app/Domains/WorkOrder/Services/OfflineSyncService.php` — menjalankan ulang aksi yang tertunda persis seperti kalau dikerjakan online (memakai `WorkOrderService` yang sama, jadi tetap menghormati aturan transisi status).
- **`routes/sync.php`** diisi (`POST /api/sync/push`, `GET /api/sync/status`). Sengaja dipindah ke middleware sesi (`web` + `auth`), **bukan** `auth:sanctum` seperti rencana awal — karena paket Laravel Sanctum belum terpasang di project ini, dan endpoint ini memang hanya dipanggil dari sesi mekanik yang sudah login di browser yang sama, jadi autentikasi sesi sudah cukup tanpa perlu tambah dependency baru.
- **`bootstrap/app.php`** disesuaikan: grup route `sync.php` pindah dari middleware `api` ke `web`, dan endpoint `api/sync/push` dikecualikan dari validasi CSRF (dipanggil dari Service Worker di background, tidak selalu punya token CSRF terbaru; keamanan tetap dijaga lewat sesi login).
- **`routes/pwa.php`** diisi rute halaman fallback `/mobile/offline` (`resources/views/mobile/offline.blade.php`).
- **`public/sw.js`** dirombak (v2): cache halaman `/mobile`, `/mobile/scanner`, `/mobile/wo` untuk dibuka saat offline, fallback ke halaman `/mobile/offline` kalau cache juga kosong, dan antrian offline yang lebih aman (item yang ditolak server dengan error 4xx dibuang agar tidak nyangkut selamanya, item yang gagal karena masih offline/error server tetap disimpan untuk dicoba lagi).
- **Indikator status koneksi**: pita kecil di atas layar mobile (`resources/views/layouts/mobile.blade.php`) yang menunjukkan "Sedang offline" atau "Menyinkronkan N aksi tertunda...".

### Catatan & batasan untuk sesi berikutnya

- Belum ada file ikon PWA asli (`/icons/pwa-192.png`, `/icons/pwa-512.png`) yang dirujuk `public/manifest.json` — perlu ditambahkan supaya prompt "Install App" tampil rapi di HP mekanik.
- Offline sync baru mencakup 3 aksi mekanik: mulai SPK, selesai SPK, tambah sparepart ke SPK dari scanner. Aksi kasir/pembayaran **sengaja tidak dibuat offline** karena menyangkut uang & butuh koneksi real-time untuk validasi stok/duplikasi transaksi.
- Setelah pull: tidak perlu `composer install` (tidak ada package baru), tapi **wajib** `npm run build` (atau `npm run dev` saat development) karena ada file JS baru (`resources/js/offline-sync.js`) yang perlu di-bundle ulang oleh Vite, dan **wajib** `php artisan route:clear && php artisan config:clear` karena `bootstrap/app.php` dan `routes/sync.php`/`routes/pwa.php` berubah.

---

## Sesi 11: Kasir Full-Screen, Sidebar Collapse & Scroll Memory, Middleware Role, Ikon PWA, Stok Check Keranjang

### Konteks

Melanjutkan 4 sisa pekerjaan dari Sesi 10, sekaligus permintaan baru dari user:
- Halaman Kasir tampil full-screen (sidebar tersembunyi secara default saat masuk kasir).
- Ada tombol untuk sembunyikan/tampilkan sidebar, dan tombolnya bisa dipakai di semua halaman (tidak cuma Kasir).
- Posisi scroll sidebar tidak reset ke atas setiap kali pindah halaman (klik menu) — sidebar harus mengingat posisi scroll terakhirnya.

### Yang dibangun

1. **Sidebar bisa disembunyikan/ditampilkan di semua halaman** (`resources/views/components/layouts/app.blade.php`):
   - Tombol baru di topbar (ikon garis tiga, khusus tampilan desktop, di sebelah hamburger mobile) untuk sembunyikan/tampilkan sidebar kapan saja, di halaman mana saja.
   - Prop baru `startCollapsed` pada layout — kalau `true`, sidebar otomatis tersembunyi saat halaman pertama dibuka, tapi user tetap bisa menampilkannya lagi lewat tombol topbar (tombolnya selalu ada, tidak ikut tersembunyi).
   - `app/Livewire/Pos/Cashier.php` sekarang mengirim `startCollapsed: true` ke layout, jadi begitu masuk Kasir langsung tampil full-screen tanpa sidebar.
2. **Sidebar mengingat posisi scroll terakhir** — memakai `sessionStorage` (bertahan selama tab browser terbuka). Setiap kali menu diklik dan halaman pindah (ini bukan SPA, jadi selalu full page reload), sidebar otomatis di-scroll balik ke posisi terakhir alih-alih mulai dari atas lagi.
3. **Middleware Role di level route** — menutup celah keamanan lama (hak akses sebelumnya cuma menyembunyikan menu di sidebar, user yang tahu URL langsung tetap bisa buka halamannya):
   - `app/Http/Middleware/EnsureRoleHasAccess.php` (baru) — mengecek role user terhadap `RoleRegistry::access()` (tabel `role_settings`, fallback ke `config/roles.php`) untuk setiap halaman yang dibuka. Kalau tidak diizinkan → halaman "Akses Ditolak" (403).
   - Didaftarkan sebagai alias `role.access` di `bootstrap/app.php`, dipasang di route halaman utama & mobile mekanik di `routes/web.php`.
   - Halaman baru `resources/views/errors/403.blade.php` — pesan "Akses Ditolak" bergaya BengkelOS, bukan halaman error polos bawaan Laravel.
   - Route yang belum punya aturan akses spesifik (Dashboard, Kasir, SPK, Pelanggan, Katalog, dll.) tetap terbuka untuk semua user login, sama seperti perilaku menu sidebar sebelumnya — tidak ada menu yang mendadak terkunci untuk siapa pun.
4. **Ikon PWA** — `public/icons/pwa-icon.svg` (baru, format SVG jadi tidak perlu file gambar biner terpisah) dipasang di `public/manifest.json`, menggantikan referensi file PNG yang sebelumnya belum ada. Prompt "Install App" di HP mekanik sekarang tampil dengan ikon yang benar.
5. **Stok check saat menambah sparepart ke keranjang kasir** (`app/Livewire/Pos/Cashier.php`):
   - `addCatalogItem()` sekarang mengecek sisa stok sparepart di cabang aktif sebelum menambah ke SPK — kalau stok tidak cukup, muncul notifikasi peringatan dan item tidak ditambahkan.
   - `changeLineQuantity()` (tombol +/- di keranjang) juga dicek — menambah jumlah sparepart di keranjang tidak bisa melebihi sisa stok cabang.
   - Jasa/layanan (bukan sparepart) tidak dicek stok karena memang tidak punya stok fisik. Kalau belum ada data stok sama sekali untuk kombinasi produk+cabang tersebut (belum pernah diinput lewat modul Inventory), sparepart tetap boleh ditambahkan supaya data yang belum lengkap tidak memblokir transaksi.

### Insiden kecil & perbaikannya

- Saat push pertama sesi ini, `bootstrap/app.php` sempat ter-upload tidak lengkap (terpotong) karena kesalahan saat menyusun isi file, sehingga sempat merusak konfigurasi aplikasi selama beberapa saat. Langsung terdeteksi dan diperbaiki di push berikutnya dengan isi file lengkap + tambahan alias `role.access`. Tidak ada perubahan lain yang terdampak.

### Catatan untuk sesi berikutnya

- Tombol "Cari Invoice" di Quick Actions kasir saat ini masih mengarah ke halaman Kasir itu sendiri (bukan pencarian invoice sungguhan) — berfungsi (tidak error) tapi kurang berguna, perlu didesain ulang kalau mau dibuat pencarian invoice yang sesungguhnya.
- Setelah pull: wajib `php artisan route:clear && php artisan config:clear` karena ada middleware baru & perubahan route.

---

## Sesi 12: Pengaturan Jaringan Lokal (LAN) & Pengaturan Printer

### Konteks

User berencana agar mode offline BengkelOS bisa diakses lewat jaringan lokal (WiFi) — jadi kalau internet mati, mekanik/kasir tetap bisa mengakses aplikasi lewat alamat IP lokal server. User juga minta pengaturan printer: printer laporan di kertas A4 (untuk printer inkjet seperti Epson/Canon), dan printer struk di printer thermal 58mm ("58x40").

### Yang dibangun

1. **Penyimpanan pengaturan generik (`app_settings`)** — migration tabel key-value (`key` string primary, `value` json) + model `App\Domains\MasterData\Models\AppSetting` dengan helper statis `get()`, `set()`, `getMany()`, `setMany()`. Dipakai sebagai penyimpanan untuk dua halaman pengaturan baru di bawah, dan dirancang supaya bisa dipakai lagi untuk pengaturan lain di masa depan tanpa perlu tabel baru setiap kali.
2. **Halaman Jaringan Lokal (LAN)** (`/pengaturan/jaringan`, route `settings.network`) — `app/Livewire/Settings/NetworkSettings.php` + view:
   - Mendeteksi otomatis alamat IP lokal server (lewat `hostname -I` di Linux, fallback `gethostbyname`), dengan tombol "deteksi ulang".
   - Field IP (bisa ditimpa manual) dan Port, serta saklar aktif/nonaktifkan mode LAN.
   - Menampilkan alamat akses LAN lengkap (`http://IP:PORT`) yang bisa langsung dibagikan ke HP mekanik/kasir.
   - Panduan langkah-langkah cara memakainya saat internet mati, termasuk contoh perintah `php artisan serve --host=0.0.0.0 --port=...` (server wajib mendengarkan di `0.0.0.0`, bukan `127.0.0.1`, agar bisa diakses dari perangkat lain di WiFi yang sama).
3. **Halaman Pengaturan Printer** (`/pengaturan/printer`, route `settings.printer`) — `app/Livewire/Settings/PrinterSettings.php` + view, dua bagian:
   - **Printer Laporan (A4)**: nama printer (label saja), ukuran kertas (A4/Letter/F4), orientasi (potret/lanskap).
   - **Printer Struk (Thermal)**: nama printer, lebar kertas (58mm/80mm), ukuran font struk, saklar auto-cut.
4. **Struk kasir memakai pengaturan printer thermal** (`app/Livewire/Pos/Cashier.php` + `cashier.blade.php`):
   - Computed property baru `printerSettings` membaca lebar kertas & ukuran font dari `AppSetting`.
   - Blok `#print-receipt` sekarang pakai lebar (px, dikonversi dari mm) dan ukuran font dinamis, bukan `320px`/`13px` hardcode.
   - CSS cetak (`@media print`) menambahkan `@page { size: <lebar>mm auto; margin: 0 }` supaya ukuran halaman cetak browser mengikuti lebar kertas thermal yang diatur (58mm/80mm), bukan ukuran default A4.
5. **Laporan bisa dicetak di A4** (`app/Livewire/Reports/Index.php` + `reports/index.blade.php`):
   - Computed property baru `reportPrintSettings` membaca ukuran kertas & orientasi dari `AppSetting`.
   - Tombol baru "Cetak Laporan" di halaman Laporan (memanggil `window.print()`).
   - CSS cetak baru: `@page { size: <ukuran> <orientasi>; margin: 12mm }`, plus trik "sembunyikan semua kecuali area laporan" (`visibility: hidden` di seluruh body, `visibility: visible` khusus area laporan) supaya sidebar/topbar/filter tidak ikut tercetak — tanpa perlu mengubah `layouts/admin.blade.php` atau `layouts/app.blade.php`.
6. **Route, hak akses & menu:**
   - `routes/web.php` — dua route baru `settings.network` (`/pengaturan/jaringan`) dan `settings.printer` (`/pengaturan/printer`), dipasang di grup middleware `['auth', 'role.access']` yang sama dengan halaman Pengaturan lain.
   - `config/roles.php` — akses kedua halaman ini dibatasi untuk **owner & admin**.
   - `app/Domains/MasterData/Services/RoleRegistry::manageableRoutes()` — kedua halaman ditambahkan supaya bisa diatur juga lewat halaman Role & Hak Akses (`/pengaturan/role`) kalau owner mau membatasi lebih lanjut.
   - `resources/views/components/sidebar-addons.blade.php` — dua menu baru di grup "Pengaturan": "Jaringan Lokal (LAN)" (ikon WiFi) dan "Pengaturan Printer" (ikon printer).

### Insiden kecil & perbaikannya

- Pada draf pertama `NetworkSettings.php`, method `getLanUrlProperty()` sempat memakai sintaks placeholder yang salah (bukan string PHP biasa) sehingga alamat LAN akan tampil rusak. Terdeteksi segera dan diperbaiki di push berikutnya menjadi penggabungan string biasa (`'http://' . $ip . ':' . $port`).

### Catatan & batasan untuk sesi berikutnya

- **Setelah pull, wajib jalankan `php artisan migrate` lagi** — ada tabel baru `app_settings`.
- Nama printer yang diisi di halaman Pengaturan Printer sifatnya hanya catatan/label di sistem — pemilihan printer fisik & driver tetap lewat kotak dialog cetak bawaan browser/OS saat tombol cetak ditekan (browser tidak punya akses langsung ke driver printer OS dari halaman web biasa).
- Mode akses LAN memanfaatkan jaringan WiFi/router yang sama antara server dan perangkat mekanik/kasir — ini bukan mengubah cara Laravel dijalankan, jadi kalau servernya dijalankan produksi (Nginx/Apache), pastikan juga servernya mendengarkan di semua alamat (`0.0.0.0`) dan port-nya dibuka di firewall, sesuai catatan di halaman Jaringan Lokal (LAN).
- Deteksi IP lokal otomatis mengandalkan `shell_exec` dan `hostname -I` (khas Linux); kalau `shell_exec` dimatikan di `php.ini` (umum di beberapa shared hosting) atau di Windows, sistem otomatis jatuh ke `gethostbyname(gethostname())` sebagai fallback — IP tetap bisa diisi manual kalau deteksi otomatis kurang akurat.

---

## Sesi 13: Perbaikan Dashboard Analitik, Komisi Mekanik, Mode Mekanik + 2 Profit Mekanik, Margin/Harga Pokok Khusus Owner, Riwayat Harga Jual

### Konteks

User melaporkan 3 halaman error (Dashboard Analitik, Komisi Mekanik, seluruh Mode Mekanik), sekaligus minta 3 fitur baru dari sisi owner:
1. Mekanik punya 2 jenis profit terakumulasi bulanan: (a) komisi "kendaraan masuk" (tetap seperti sebelumnya), (b) bonus KPI/target pekerjaan bulanan (cair penuh kalau target tercapai).
2. Margin & harga pokok (harga beli) hanya boleh terlihat/diatur oleh akun role **Owner**; margin bisa diset per item atau per kategori item.
3. Riwayat perubahan harga jual, dengan kolom keterangan/alasan perubahan.

### Akar masalah 3 bug yang dilaporkan

1. **Dashboard Analitik error** — `app/Domains/Analytics/Services/AnalyticsService.php` melakukan query ke tabel `invoice_items` yang **tidak pernah ada** di database (rincian item transaksi sebenarnya disimpan di `work_order_items`, terhubung ke `invoices` lewat `invoices.work_order_id`). Semua fungsi grafik (omzet per jasa/sparepart, sparepart terlaris, jasa terlaris, komposisi omzet) selalu gagal dengan SQL error. Fungsi `mechanicPerformance()` juga salah join (`mc.mechanic_id` padahal kolom aslinya `mc.user_id`).
2. **Komisi Mekanik error** — `app/Livewire/Commission/Index.php` memakai nama kolom yang tidak ada di `work_orders` (`mechanic_id`, `grand_total`, `labor_cost`, `completed_at` — saat itu belum ada), status `'done'` yang tidak valid (status asli: `Pending`/`In Progress`/`Completed`/`Paid`), dan role string `'mechanic'` (seharusnya `'mekanik'`).
3. **Mode Mekanik error semua** — Kolom `work_orders.status` di database berisi nilai lama yang tidak cocok dengan enum `WorkOrderStatus` yang berlaku sekarang, sehingga setiap kali Eloquent mencoba mem-baca baris SPK, terjadi `ValueError` (enum tidak valid) dan seluruh halaman `/mobile`, `/mobile/wo` gagal dimuat. Ditambah bug lama `$wo->vehicle?->model_name` yang memanggil kolom yang tidak ada (kendaraan hanya punya kolom `brand` dan `type`).

### Perbaikan bug (semua sudah di-push ke `main`)

- **Migration baru** `2026_09_06_000001_normalize_and_extend_work_orders.php` — menormalkan nilai `status` lama yang tidak valid di tabel `work_orders` menjadi salah satu dari `Pending/In Progress/Completed/Paid`, sekaligus menambah kolom `wo_number`, `completed_at`, `paid_at` (dipakai penomoran SPK & pencatatan waktu selesai/lunas).
- `app/Domains/Analytics/Services/AnalyticsService.php` — ditulis ulang: semua query yang tadinya ke `invoice_items` sekarang ke `work_order_items` join `invoices` (lewat `work_order_id`); `mechanicPerformance()` diperbaiki joinnya ke `mc.user_id`; nama kolom nominal invoice dideteksi otomatis (`amountColumn()`) supaya tidak gampang error lagi kalau skema berubah.
- `app/Livewire/Commission/Index.php` + `resources/views/livewire/commission/index.blade.php` — ditulis ulang total, sekarang memakai `CommissionService` (lihat di bawah), filter per bulan, tabel rekap per mekanik (WO selesai, total jasa, komisi kendaraan, status target KPI, bonus KPI, total komisi), dan drill-down daftar SPK per mekanik.
- `resources/views/livewire/mobile/home.blade.php` dan `resources/views/livewire/mobile/wo.blade.php` — `$wo->vehicle?->model_name` diganti menjadi gabungan `brand` + `type` kendaraan yang benar-benar ada di database.

### Fitur baru 1 — 2 Profit Bulanan Mekanik

- Tabel `mechanic_commissions` (sudah ada) ditambah kolom `source` (migration `2026_09_06_000002_add_kpi_and_commission_fields.php`), plus kolom baru di tabel `users`: `monthly_target` (target jumlah SPK selesai per bulan) dan `kpi_bonus_amount` (nominal bonus kalau target tercapai).
- Model baru `app/Domains/Commission/Models/MechanicCommission.php` dan service baru `app/Domains/Commission/Services/CommissionService.php`:
  - **Profit 1 — Kendaraan Masuk:** setiap kali SPK berpindah status ke **Paid**, `WorkOrderService::markPaid()` otomatis memanggil `CommissionService::recordForWorkOrder()` yang mencatat satu baris komisi (persen dari nilai jasa) di `mechanic_commissions` — perilaku ini sama seperti sebelumnya, tidak berubah.
  - **Profit 2 — Bonus KPI:** `CommissionService::monthlySummary()` menghitung jumlah SPK selesai mekanik dalam sebulan; kalau jumlahnya sudah mencapai `monthly_target` milik mekanik tersebut, `kpi_bonus_amount` otomatis ditambahkan sebagai profit kedua bulan itu.
- Halaman **Manajemen User** (`app/Livewire/Settings/UserManagement.php` + view) — ditambah 2 input baru khusus role Mekanik: "Target KPI / Bulan" dan "Bonus KPI (Rp)", tampil di form tambah/ubah user.
- Halaman **Komisi Mekanik** (lihat di atas) menampilkan kedua profit ini secara terpisah per mekanik, plus total gabungan.

### Fitur baru 2 — Margin & Harga Pokok Khusus Owner + Margin per Kategori

- Migration baru `2026_09_06_000003_add_margin_category_and_price_history.php` — menambah kolom `category` dan `margin_percent` di tabel `products`, plus tabel baru `category_margins` (margin default per kategori) dan `product_price_histories` (riwayat perubahan harga, lihat fitur 3).
- Model baru `app/Domains/Catalog/Models/CategoryMargin.php`.
- `app/Livewire/Catalog/Index.php` ditulis ulang — computed property `isOwner()` (cek `role === 'owner'`) menentukan apakah field Harga Beli & Margin ditampilkan/bisa diisi. Untuk akun selain Owner, field-field ini **tidak pernah dikirim ke browser sama sekali** (bukan cuma disembunyikan lewat CSS) supaya data harga pokok benar-benar tidak bocor.
- `resources/views/livewire/catalog/index.blade.php` — kolom "Harga Beli" & "Margin" di tabel sparepart, serta field-nya di form tambah/ubah, hanya tampil untuk Owner (ada catatan info untuk role lain bahwa data ini dikunci). Ditambah input Kategori (dengan datalist saran) yang tetap terlihat semua role, dan tombol "Margin per Kategori" (khusus Owner) untuk mengatur margin default per kategori (misalnya semua item kategori "Oli" otomatis margin 20% kecuali item itu punya margin sendiri).

### Fitur baru 3 — Riwayat Perubahan Harga Jual + Keterangan

- Tabel baru `product_price_histories` (dari migration yang sama di atas): `product_id`, `old_cost_price`, `new_cost_price`, `old_sell_price`, `new_sell_price`, `note` (keterangan), `changed_by` (siapa yang mengubah).
- Model baru `app/Domains/Catalog/Models/ProductPriceHistory.php`.
- `Catalog/Index.php::saveProduct()` — setiap kali harga beli atau harga jual sparepart berubah, sistem **mewajibkan** kolom keterangan diisi (mis. "harga naik dari supplier"), lalu otomatis mencatat satu baris riwayat sebelum menyimpan perubahan harga.
- Tombol "Riwayat" baru di setiap baris sparepart membuka modal yang menampilkan seluruh riwayat perubahan harga (tanggal, siapa yang mengubah, harga lama → baru, dan keterangannya). Kolom Harga Beli di riwayat ini juga ikut disembunyikan untuk role selain Owner.

### File yang diubah/ditambah sesi ini

- Migration: `2026_09_06_000001_normalize_and_extend_work_orders.php`, `2026_09_06_000002_add_kpi_and_commission_fields.php`, `2026_09_06_000003_add_margin_category_and_price_history.php`
- Model baru: `app/Domains/Catalog/Models/CategoryMargin.php`, `app/Domains/Catalog/Models/ProductPriceHistory.php`, `app/Domains/Commission/Models/MechanicCommission.php`
- Service baru: `app/Domains/Commission/Services/CommissionService.php`
- Diubah: `app/Domains/Catalog/Models/Product.php`, `app/Domains/WorkOrder/Models/WorkOrder.php`, `app/Models/User.php`, `app/Domains/WorkOrder/Services/WorkOrderService.php`, `app/Domains/Analytics/Services/AnalyticsService.php`, `app/Livewire/Commission/Index.php`, `resources/views/livewire/commission/index.blade.php`, `app/Livewire/Settings/UserManagement.php`, `resources/views/livewire/settings/user-management.blade.php`, `app/Livewire/Catalog/Index.php`, `resources/views/livewire/catalog/index.blade.php`, `resources/views/livewire/mobile/home.blade.php`, `resources/views/livewire/mobile/wo.blade.php`

### Catatan penting untuk sesi berikutnya

- **Setelah pull, wajib jalankan `php artisan migrate` lagi** — ada 3 migration baru sesi ini (normalisasi status SPK + kolom KPI/komisi + margin kategori & riwayat harga).
- Rate komisi "kendaraan masuk" default diambil dari `.env` (`COMMISSION_RATE`, fallback 10%), bisa ditimpa per mekanik lewat field "Rate Komisi Khusus (%)" yang sudah ada sejak sebelumnya di Manajemen User.
- Kalau mekanik belum diisi "Target KPI / Bulan", bonus KPI otomatis tidak dihitung (dianggap belum diaktifkan) — tidak akan error, hanya kosong "Belum diset" di halaman Komisi Mekanik.
- Margin per kategori bersifat *default* — kalau sebuah sparepart sudah diisi margin sendiri (field Margin di form produk), margin itu yang dipakai; margin kategori hanya jadi acuan untuk item yang belum diisi margin sendiri (perhitungan margin efektif final ke harga jual disarankan tetap dicek manual sampai ada halaman kalkulator margin otomatis).

---

## Status Terbaru (setelah Sesi 13)

### Yang Sudah Selesai (tambahan dari sesi-sesi sebelumnya):
32. ✅ Dashboard Analitik diperbaiki (query yang salah ke tabel `invoice_items` yang tidak ada, sekarang pakai `work_order_items`)
33. ✅ Komisi Mekanik ditulis ulang total, sesuai skema database yang berlaku (tidak error lagi)
34. ✅ Mode Mekanik (`/mobile`, `/mobile/wo`) diperbaiki — status SPK lama dinormalisasi, bug `model_name` diperbaiki
35. ✅ 2 profit bulanan mekanik: komisi "kendaraan masuk" (tetap) + bonus KPI/target pekerjaan bulanan
36. ✅ Margin & harga pokok (harga beli) hanya terlihat/bisa diatur oleh role Owner
37. ✅ Margin bisa diset per item atau per kategori item (`/katalog` → tombol "Margin per Kategori", khusus Owner)
38. ✅ Riwayat perubahan harga jual & harga beli, dengan kolom keterangan wajib diisi

### Yang Perlu Dilanjutkan:
1. 🔲 Redesign tombol "Cari Invoice" di Quick Actions kasir agar benar-benar mencari invoice (saat ini hanya link balik ke halaman Kasir)
2. 🔲 Halaman kalkulator margin otomatis (hitung harga jual otomatis dari harga beli + margin kategori/item) — saat ini margin & harga jual masih diinput manual terpisah

### File Yang Perlu Diperhatikan (tambahan Sesi 13):
- `app/Domains/Analytics/Services/AnalyticsService.php` - Dashboard Analitik, sumber data KPI/grafik owner — diperbaiki total Sesi 13
- `app/Domains/Commission/Services/CommissionService.php` - Sumber tunggal perhitungan 2 profit mekanik (komisi kendaraan + bonus KPI) — baru Sesi 13
- `app/Domains/Commission/Models/MechanicCommission.php` - Model komisi per SPK — baru Sesi 13
- `app/Livewire/Commission/Index.php` + `resources/views/livewire/commission/index.blade.php` - Halaman Komisi Mekanik — ditulis ulang Sesi 13
- `app/Domains/Catalog/Models/CategoryMargin.php` - Margin default per kategori item — baru Sesi 13
- `app/Domains/Catalog/Models/ProductPriceHistory.php` - Riwayat perubahan harga jual/beli — baru Sesi 13
- `app/Livewire/Catalog/Index.php` + `resources/views/livewire/catalog/index.blade.php` - Katalog, margin/harga pokok khusus Owner + riwayat harga — ditulis ulang Sesi 13
- `app/Livewire/Settings/UserManagement.php` + view - tambah Target KPI & Bonus KPI mekanik — Sesi 13
- `resources/views/livewire/mobile/{home,wo}.blade.php` - bug `model_name` diperbaiki — Sesi 13

### Known Issues:
- Tombol "Cari Invoice" di kasir belum jadi pencarian invoice sungguhan (masih link balik ke halaman Kasir)
- Margin per kategori/item masih input manual (belum ada kalkulator otomatis harga jual dari harga beli + margin)
