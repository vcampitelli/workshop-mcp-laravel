<?php

declare(strict_types=1);

namespace App\Domains\OAuth\CompleteOAuthDemo;

enum CompleteOAuthDemoError
{
    case InvalidState;
    case AccessDenied;
    case AuthorizationFailed;
    case MissingCode;
    case TokenExchangeFailed;
}
