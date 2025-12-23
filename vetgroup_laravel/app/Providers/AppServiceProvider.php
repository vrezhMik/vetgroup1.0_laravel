<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    // TODO: uncomment
    // public function boot(): void
    // {
    //     URL::forceScheme('https');
    // }
    public function boot(): void
    {
    }

}

