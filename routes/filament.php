<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    // Filament panel routes are auto-registered by the panel provider.
});
