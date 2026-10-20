<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domains\OAuth\CompleteOAuthDemo\CompleteOAuthDemoError;
use App\Domains\OAuth\CompleteOAuthDemo\CompleteOAuthDemoUseCase;
use App\Domains\OAuth\CompleteOAuthDemo\CompleteOAuthDemoUseCaseInput;
use App\Domains\OAuth\StartOAuthDemo\OAuthDemoSession;
use App\Domains\OAuth\StartOAuthDemo\StartOAuthDemoUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OAuthDemoController extends Controller
{
    public function start(Request $request, StartOAuthDemoUseCase $useCase): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $output = $useCase($request->string('ticket')->toString());
        $session = $output->session;
        abort_if($session === null, 410, 'Link expirado ou já utilizado. Execute php artisan oauth:demo novamente.');
        $request->session()->put('oauth_demo', $session);

        return redirect(url('/oauth/authorize').'?'.http_build_query([
            'client_id' => $session->clientId,
            'redirect_uri' => $session->redirectUri,
            'response_type' => 'code',
            'scope' => $session->scope,
            'state' => $session->state,
            'code_challenge' => $output->challenge,
            'code_challenge_method' => 'S256',
            'prompt' => 'consent',
        ], '', '&', PHP_QUERY_RFC3986));
    }

    public function callback(Request $request, CompleteOAuthDemoUseCase $useCase): Response
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $session = $request->session()->get('oauth_demo');
        $state = $request->query('state');
        $code = $request->query('code');
        $authorizationError = $request->query('error');
        $output = $useCase(CompleteOAuthDemoUseCaseInput::fromArray([
            'session' => $session instanceof OAuthDemoSession ? $session : null,
            'state' => is_string($state) ? $state : null,
            'code' => is_string($code) ? $code : null,
            'authorization_error' => is_string($authorizationError) ? $authorizationError : null,
        ]));
        abort_if($output->error === CompleteOAuthDemoError::InvalidState, 419, 'Estado inválido ou demonstração expirada.');
        $request->session()->forget('oauth_demo');

        $error = match ($output->error) {
            CompleteOAuthDemoError::AccessDenied => 'Você recusou o acesso. Nenhum token foi emitido.',
            CompleteOAuthDemoError::MissingCode => 'O callback não recebeu um código de autorização.',
            CompleteOAuthDemoError::TokenExchangeFailed => 'O Passport rejeitou a troca do código por token. Execute o comando novamente.',
            null => null,
            default => 'A autorização não foi concluída. Execute o comando novamente.',
        };
        $status = $output->error === null || $output->error === CompleteOAuthDemoError::AccessDenied ? 200 : 400;

        return response()->view('auth.oauth-demo', ['token' => $output->token, 'error' => $error], $status)
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
