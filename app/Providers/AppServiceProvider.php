<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Si la app se sirve por HTTPS (APP_URL), forzar ese esquema en todas
        // las URLs generadas. Evita que redirect()/route() devuelvan http:// y
        // que la redirección http→https posterior descarte la cabecera X-Inertia
        // (síntoma: el login abre el dashboard dentro de un modal raro).
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
