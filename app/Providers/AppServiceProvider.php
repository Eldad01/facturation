<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

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
        Schema::defaultStringLength(191);

        /**
         * 1️⃣ Injection globale des paramètres de l'application
         */
        view()->composer('*', function ($view) {

            $settings = Setting::first();

            // Si la table settings est vide → on purge le cache
            if (!$settings) {
                Cache::forget('app_settings');
            }

            $view->with('app_settings', $settings);
        });
    }
}
