<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Register the Horizon gate.
     *
     * Only users whose email is in the allowed list
     * can access the Horizon dashboard.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user) {
            return $user->hasRole(['admin', 'super-admin']) || in_array($user->email, array_filter([
                env('ADMIN_SEED_EMAIL'),
                config('app.admin_email'),
            ]));
        });
    }
}