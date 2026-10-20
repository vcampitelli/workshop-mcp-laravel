<?php

declare(strict_types=1);

namespace App\Domains\OAuth\StartOAuthDemo;

final readonly class OAuthDemoSession
{
    public function __construct(
        public string $clientId,
        public string $redirectUri,
        public string $scope,
        public string $state,
        public string $verifier,
        public int $expiresAt,
    ) {}
}
