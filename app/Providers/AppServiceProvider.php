<?php

namespace App\Providers;

use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {/*
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/personal/public/livewire/update', $handle);
        });*/
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
