<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Passport\Http\Controllers\AccessTokenController;

class OAuthDemoController extends Controller
{
    public function start(Request $request)
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $ticket = (string) $request->query('ticket');
        $pending = Cache::lock('oauth-demo-lock:'.$ticket, 5)->block(
            3, fn () => Cache::pull('oauth-demo:'.$ticket),
        );
        abort_unless(is_array($pending), 410, 'Link expirado ou já utilizado. Execute php artisan oauth:demo novamente.');

        $verifier = Str::random(64);
        $state = Str::random(64);
        $request->session()->put('oauth_demo', $pending + [
            'state' => $state,
            'verifier' => $verifier,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect(url('/oauth/authorize').'?'.http_build_query([
            'client_id' => $pending['client_id'],
            'redirect_uri' => $pending['redirect_uri'],
            'response_type' => 'code',
            'scope' => $pending['scope'],
            'state' => $state,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
            'prompt' => 'consent',
        ], '', '&', PHP_QUERY_RFC3986));
    }

    public function callback(Request $request, AccessTokenController $tokens)
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $pending = $request->session()->get('oauth_demo');
        $state = $request->query('state');
        abort_unless(is_array($pending) && is_string($state)
            && hash_equals($pending['state'], $state)
            && $pending['expires_at'] >= now()->timestamp, 419, 'Estado inválido ou demonstração expirada.');
        $request->session()->forget('oauth_demo');

        $token = null;
        $error = null;
        $status = 200;

        if ($request->query('error') === 'access_denied') {
            $error = 'Você recusou o acesso. Nenhum token foi emitido.';
        } elseif ($request->has('error')) {
            $error = 'A autorização não foi concluída. Execute o comando novamente.';
            $status = 400;
        } elseif (! is_string($request->query('code')) || $request->query('code') === '') {
            $error = 'O callback não recebeu um código de autorização.';
            $status = 400;
        } else {
            // Use o endpoint real do Passport sem fazer uma chamada HTTP ao próprio
            // servidor, o que bloquearia o servidor de desenvolvimento de uma thread.
            $response = $tokens->issueToken(
                (new ServerRequest('POST', url('/oauth/token')))->withParsedBody([
                    'grant_type' => 'authorization_code',
                    'client_id' => $pending['client_id'],
                    'redirect_uri' => $pending['redirect_uri'],
                    'code_verifier' => $pending['verifier'],
                    'code' => $request->query('code'),
                ]),
                new Response,
            );

            if ($response->isSuccessful()) {
                $token = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
            } else {
                $error = 'O Passport rejeitou a troca do código por token. Execute o comando novamente.';
                $status = 400;
            }
        }

        return response()->view('auth.oauth-demo', compact('token', 'error'), $status)
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }
}
