<?php

namespace App\Providers;

use App\Support\Mail\SendGridApiTransport;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        Mail::extend('sendgrid', function (array $config) {
            return new SendGridApiTransport($config['api_key'] ?? '');
        });
    }
}
