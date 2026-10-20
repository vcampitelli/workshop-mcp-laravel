<?php

declare(strict_types=1);

namespace App\Domains\OAuth\StartOAuthDemo;

use App\Domains\Shared\UseCaseError;

final readonly class StartOAuthDemoUseCaseOutput
{
    public function __construct(
        public ?OAuthDemoSession $session = null,
        public ?string $challenge = null,
        public ?UseCaseError $error = null,
    ) {}
}
