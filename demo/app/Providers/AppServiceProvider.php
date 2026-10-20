<?php

namespace App\Providers;

use App\Domains\OAuth\Contracts\TokenIssuer;
use App\Infrastructure\OAuth\PassportTokenIssuer;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TokenIssuer::class, PassportTokenIssuer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var array<string, string> $permissions */
        $permissions = config('permissions');
        Passport::tokensCan($permissions);
        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addDays(7));
        Passport::authorizationView('auth.authorize');
    }
}
