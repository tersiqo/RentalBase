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
        if ($this->app->environment('production') || str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
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

        // 15 In-App Notification Triggers Wiring
        \Illuminate\Support\Facades\Event::listen([
            \App\Events\OrderConfirmed::class,
            \App\Events\IdentityApproved::class,
            \App\Events\IdentityRejected::class,
            \App\Events\PaymentVerified::class,
            \App\Events\PaymentRejected::class,
            \App\Events\OrderShipped::class,
            \App\Events\ReturnAccepted::class,
            \App\Events\FineIssued::class,
            \App\Events\DamageCaseCreated::class,
            \App\Events\DamageCaseResolved::class,
            \App\Events\NewOrderPlaced::class,
            \App\Events\IdentityProofUploaded::class,
            \App\Events\PaymentProofUploaded::class,
            \App\Events\ReturnRequested::class,
            \App\Events\TenantRegistrationSubmitted::class,
            \App\Events\SubscriptionRenewalUploaded::class,
            \App\Events\TenantQuotaAlert::class,
        ], \App\Listeners\SendInAppNotification::class);
    }
}
