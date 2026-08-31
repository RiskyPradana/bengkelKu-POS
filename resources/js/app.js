import './bootstrap';
import '../css/app.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

window.addEventListener('livewire:init', () => {
    window.addEventListener('refreshCashier', () => {
        if (window.Livewire) {
            window.Livewire.dispatch('refreshCashier');
        }
    });
});
