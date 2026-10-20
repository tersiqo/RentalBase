<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        // Otorisasi fitur khusus Admin Utama toko
        Gate::define('manage-store-admin', function (User $user) {
            return $user->isPrimaryAdmin();
        });

        Gate::define('manage-store-catalog', function (User $user) {
            return $user->isPrimaryAdmin();
        });

        Gate::define('manage-store-branding', function (User $user) {
            return $user->isPrimaryAdmin();
        });
    }
}
