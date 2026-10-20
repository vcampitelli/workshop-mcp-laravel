<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class StartOAuthDemo extends Command
{
    protected $signature = 'oauth:demo {--scope=* : Permissão solicitada (pode ser repetida)}';

    protected $description = 'Inicia uma demonstração real de OAuth2 Authorization Code com PKCE';

    public function handle(ClientRepository $clients): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('A demonstração está disponível apenas em ambiente local.');

            return self::FAILURE;
        }

        /** @var list<string> $requestedScopes */
        $requestedScopes = $this->option('scope');
        /** @var array<string, string> $permissions */
        $permissions = config('permissions');
        $scopes = $requestedScopes ?: ['orders:list', 'orders:show'];
        $invalid = array_diff($scopes, array_keys($permissions));

        if ($invalid) {
            $this->error('Permissões desconhecidas: '.implode(', ', $invalid));

            return self::FAILURE;
        }

        if ((! config('passport.private_key') && ! is_readable(Passport::keyPath('oauth-private.key')))
            || (! config('passport.public_key') && ! is_readable(Passport::keyPath('oauth-public.key')))) {
            $this->error('Gere as chaves primeiro: php artisan passport:keys');

            return self::FAILURE;
        }

        if (! Schema::hasTable('oauth_clients') || ! Schema::hasTable('oauth_auth_codes')
            || ! Schema::hasTable('oauth_access_tokens') || ! Schema::hasTable('oauth_refresh_tokens')) {
            $this->error('Prepare as tabelas do Passport: php artisan vendor:publish --tag=passport-migrations && php artisan migrate');

            return self::FAILURE;
        }

        $callback = route('oauth.demo.callback');
        $client = Passport::client()->newQuery()
            ->where('name', 'Assistente de Compras (demo)')
            ->where('revoked', false)
            ->get()
            ->first(fn ($client) => ! $client->confidential()
                && $client->hasGrantType('authorization_code')
                && $client->getAttribute('redirect_uris') === [$callback]);

        $client ??= $clients->createAuthorizationCodeGrantClient(
            'Assistente de Compras (demo)', [$callback], confidential: false,
        );

        $ticket = Str::random(64);
        $expires = now()->addMinutes(10);

        Cache::put('oauth-demo:'.$ticket, [
            'client_id' => $client->getKey(),
            'redirect_uri' => $callback,
            'scope' => implode(' ', array_unique($scopes)),
        ], $expires);

        $this->info('Abra o link abaixo no navegador (válido por 10 minutos e para um único uso):');
        $this->line(URL::temporarySignedRoute('oauth.demo.start', $expires, ['ticket' => $ticket]));
        $this->newLine();
        $this->line('Cliente: '.$client->name);
        $this->line('Permissões: '.implode(', ', $scopes));
        $this->line('Faça login e escolha Autorizar ou Recusar. O callback exibirá o resultado.');

        return self::SUCCESS;
    }
}
