<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Domain service providers will be registered here.
    }

    public function boot(): void
    {
        // Shared module boot logic will live here.
    }
}
