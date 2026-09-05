import './bootstrap';
import '../css/app.css';

// Catatan: JANGAN import/atau jalankan Alpine.js secara manual di sini.
// @livewireScripts di layout sudah membawa & menjalankan Alpine bawaan Livewire
// sendiri (Livewire v3 dibangun sebagai plugin Alpine). Kalau Alpine dijalankan
// dua kali (di sini + oleh Livewire), Livewire kehilangan kesempatan mendaftarkan
// directive wire:* sebelum Alpine memindai halaman, sehingga tombol wire:click
// (Tambah, Edit, Hapus, toggle Status, tab, dll) terlihat tidak merespons klik.

window.addEventListener('livewire:init', () => {
    window.addEventListener('refreshCashier', () => {
        if (window.Livewire) {
            window.Livewire.dispatch('refreshCashier');
        }
    });
});
