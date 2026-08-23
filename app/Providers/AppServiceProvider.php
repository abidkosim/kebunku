<?php

namespace App\Providers;

use App\Support\SesiAktor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // scoped() = satu instance per request. Identitas "siapa yang login" jadi
        // diselesaikan sekali saja per page load, bukan diulang tiap komponen/partial.
        $this->app->scoped(SesiAktor::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
