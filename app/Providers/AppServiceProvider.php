<?php

namespace App\Providers;

use App\Http\Responses\Auth\CustomLoginResponse;
use App\Http\Responses\Auth\CustomRegistrationResponse;
use App\Models\User;
use App\Observers\UserObserver;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginResponseContract::class, CustomLoginResponse::class);
        $this->app->bind(RegistrationResponseContract::class, CustomRegistrationResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MySQL utf8mb4 key length issue
        Schema::defaultStringLength(191);

        // Register User Observer
        User::observe(UserObserver::class);
    }
}
