<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Vite;
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
        // บังคับ locale เป็นภาษาไทย เพื่อให้ translation (รวมถึง paginator labels) ใช้งานจริง
        // ป้องกันกรณี .env มี APP_LOCALE=en มาทับค่า config
        App::setLocale('th');
        Carbon::setLocale('th');

        Vite::prefetch(concurrency: 3);
    }
}
