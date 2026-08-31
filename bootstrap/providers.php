<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ModuleServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Domains\Auth\AuthServiceProvider::class,
    App\Domains\MasterData\MasterDataServiceProvider::class,
    App\Domains\CustomerVehicle\CustomerVehicleServiceProvider::class,
    App\Domains\Catalog\CatalogServiceProvider::class,
    App\Domains\WorkOrder\WorkOrderServiceProvider::class,
    App\Domains\POS\POSServiceProvider::class,
];
