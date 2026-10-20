<?php

declare(strict_types=1);

namespace App\Domains\OAuth\Contracts;

use App\Domains\OAuth\CompleteOAuthDemo\OAuthToken;
use App\Domains\OAuth\StartOAuthDemo\OAuthDemoSession;

interface TokenIssuer
{
    public function issue(OAuthDemoSession $session, string $code): ?OAuthToken;
}
