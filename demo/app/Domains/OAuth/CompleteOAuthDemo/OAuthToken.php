<?php

declare(strict_types=1);

namespace App\Domains\OAuth\CompleteOAuthDemo;

final readonly class OAuthToken
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
    ) {}
}
