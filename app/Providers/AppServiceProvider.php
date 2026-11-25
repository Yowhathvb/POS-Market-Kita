<?php

namespace App\Providers;

use App\Mail\Transports\BrevoTransport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
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
        // Cegah N+1 Query Problem secara otomatis di seluruh aplikasi
        Model::preventLazyLoading(!app()->isProduction());

        // Daftarkan transport HTTP Brevo (kompatibel dengan Wasmer tanpa SMTP)
        Mail::extend('brevo', function (array $config = []) {
            return new BrevoTransport(
                $config['key'] ?? config('services.brevo.key')
            );
        });
    }
}
