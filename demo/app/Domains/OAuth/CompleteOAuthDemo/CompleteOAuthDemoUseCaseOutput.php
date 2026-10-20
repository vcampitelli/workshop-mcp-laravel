<?php

declare(strict_types=1);

namespace App\Domains\OAuth\CompleteOAuthDemo;

final readonly class CompleteOAuthDemoUseCaseOutput
{
    public function __construct(
        public ?OAuthToken $token = null,
        public ?CompleteOAuthDemoError $error = null,
    ) {}
}
