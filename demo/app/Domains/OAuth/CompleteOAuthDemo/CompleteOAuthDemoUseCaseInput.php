<?php

declare(strict_types=1);

namespace App\Domains\OAuth\CompleteOAuthDemo;

use App\Domains\OAuth\StartOAuthDemo\OAuthDemoSession;

final readonly class CompleteOAuthDemoUseCaseInput
{
    public function __construct(
        public ?OAuthDemoSession $session,
        public ?string $state,
        public ?string $code,
        public ?string $authorizationError = null,
    ) {}
}
