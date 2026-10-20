<?php

declare(strict_types=1);

namespace App\Infrastructure\OAuth;

use App\Domains\OAuth\CompleteOAuthDemo\OAuthToken;
use App\Domains\OAuth\Contracts\TokenIssuer;
use App\Domains\OAuth\StartOAuthDemo\OAuthDemoSession;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Laravel\Passport\Http\Controllers\AccessTokenController;

final class PassportTokenIssuer implements TokenIssuer
{
    public function __construct(private AccessTokenController $tokens) {}

    public function issue(OAuthDemoSession $session, string $code): ?OAuthToken
    {
        // Avoid an HTTP round trip to the single-threaded development server.
        $response = $this->tokens->issueToken(
            (new ServerRequest('POST', url('/oauth/token')))->withParsedBody([
                'grant_type' => 'authorization_code',
                'client_id' => $session->clientId,
                'redirect_uri' => $session->redirectUri,
                'code_verifier' => $session->verifier,
                'code' => $code,
            ]),
            new Response,
        );
        if (! $response->isSuccessful()) {
            return null;
        }
        $body = $response->getContent();
        if ($body === false) {
            return null;
        }
        $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($data) || ! is_string($data['access_token'] ?? null)
            || ! is_string($data['token_type'] ?? null) || ! is_int($data['expires_in'] ?? null)) {
            return null;
        }

        return new OAuthToken($data['access_token'], $data['token_type'], $data['expires_in']);
    }
}
