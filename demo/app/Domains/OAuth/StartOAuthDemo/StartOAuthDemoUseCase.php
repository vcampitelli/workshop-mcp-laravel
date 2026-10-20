<?php

declare(strict_types=1);

namespace App\Domains\OAuth\StartOAuthDemo;

use App\Domains\Shared\UseCaseError;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class StartOAuthDemoUseCase
{
    public function __invoke(string $ticket): StartOAuthDemoUseCaseOutput
    {
        $pending = Cache::lock('oauth-demo-lock:'.$ticket, 5)->block(
            3, fn () => Cache::pull('oauth-demo:'.$ticket),
        );
        if (! is_array($pending) || ! is_string($pending['client_id'] ?? null)
            || ! is_string($pending['redirect_uri'] ?? null) || ! is_string($pending['scope'] ?? null)) {
            return new StartOAuthDemoUseCaseOutput(error: UseCaseError::NotFound);
        }
        $verifier = Str::random(64);
        $session = new OAuthDemoSession(
            clientId: $pending['client_id'], redirectUri: $pending['redirect_uri'], scope: $pending['scope'],
            state: Str::random(64), verifier: $verifier, expiresAt: now()->addMinutes(10)->getTimestamp(),
        );
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return new StartOAuthDemoUseCaseOutput($session, $challenge);
    }
}
