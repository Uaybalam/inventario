<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Inventario;
use App\Observers\InventarioObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inventario::observe(InventarioObserver::class);
    }
}
