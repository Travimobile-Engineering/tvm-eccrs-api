<?php

namespace App\Providers;

use App\Contracts\SMS;
use App\Models\Sanctum\PersonalAccessToken;
use App\Services\SMS\SmsServiceFactory;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SMS::class, function ($app) {
            $provider = config('services.sms.default');

            return SmsServiceFactory::make($provider);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
