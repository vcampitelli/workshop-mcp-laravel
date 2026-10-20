<?php

declare(strict_types=1);

namespace App\Domains\OAuth\CompleteOAuthDemo;

use App\Domains\OAuth\Contracts\TokenIssuer;

final class CompleteOAuthDemoUseCase
{
    public function __construct(private TokenIssuer $tokens) {}

    public function __invoke(CompleteOAuthDemoUseCaseInput $input): CompleteOAuthDemoUseCaseOutput
    {
        $session = $input->session;
        if ($session === null || $input->state === null || ! hash_equals($session->state, $input->state)
            || $session->expiresAt < now()->timestamp) {
            return new CompleteOAuthDemoUseCaseOutput(error: CompleteOAuthDemoError::InvalidState);
        }
        if ($input->authorizationError !== null) {
            return new CompleteOAuthDemoUseCaseOutput(error: $input->authorizationError === 'access_denied'
                ? CompleteOAuthDemoError::AccessDenied : CompleteOAuthDemoError::AuthorizationFailed);
        }
        if ($input->code === null || $input->code === '') {
            return new CompleteOAuthDemoUseCaseOutput(error: CompleteOAuthDemoError::MissingCode);
        }
        $token = $this->tokens->issue($session, $input->code);

        return $token === null
            ? new CompleteOAuthDemoUseCaseOutput(error: CompleteOAuthDemoError::TokenExchangeFailed)
            : new CompleteOAuthDemoUseCaseOutput(token: $token);
    }
}
