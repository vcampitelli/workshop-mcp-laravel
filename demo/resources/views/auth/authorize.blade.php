<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ededf0">
    <title>Autorizar acesso — Meu Ecommerce</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/authorize.css') }}">
</head>
<body>
    <main class="login-page">
        <div class="login-card authorize-card">
            <header class="authorize-brand" aria-label="Meu Ecommerce">
                <div class="brand">
                    <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
                        <rect x="1" y="1" width="11" height="11" rx="5.5" fill="currentColor"/>
                        <rect x="16" y="1" width="11" height="11" rx="5.5" fill="currentColor"/>
                        <rect x="1" y="16" width="11" height="11" rx="5.5" fill="currentColor"/>
                        <rect x="16" y="16" width="11" height="11" rx="5.5" fill="currentColor"/>
                    </svg>
                    <span>Meu Ecommerce</span>
                </div>
            </header>

            <section class="login-main authorize-main" aria-labelledby="authorize-title">
                <div class="authorize-content">
                    <header class="login-heading authorize-heading">
                        <div class="connection-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3 4 6v6c0 4 4 7 8 9 4-2 8-5 8-9V6l-8-3Z"/>
                                <path d="m8.5 12 2.5 2.5 4.5-5"/>
                            </svg>
                        </div>
                        <h1 id="authorize-title">Autorizar conexão</h1>
                        <p><strong>{{ $client->name }}</strong> quer acessar sua conta no Meu Ecommerce.</p>
                    </header>

                    <div class="connected-account">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.5"/>
                            <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                        </svg>
                        <div>
                            <span class="account-label">Conectado como {{ $user->name }}</span>
                            <span class="account-email">{{ $user->email }}</span>
                        </div>
                    </div>

                    <section class="permissions" aria-labelledby="permissions-title">
                        <h2 id="permissions-title">Este aplicativo solicita permissão para:</h2>
                        <ul class="permission-list">
                            @forelse ($scopes as $scope)
                                <li>
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m5 10 3 3 7-7"/>
                                    </svg>
                                    <div>
                                        <span class="permission-name">{{ $scope->description }}</span>
                                        <code class="permission-scope">{{ $scope->id }}</code>
                                    </div>
                                </li>
                            @empty
                                <li class="permissions-empty">Nenhuma permissão específica foi solicitada.</li>
                            @endforelse
                        </ul>
                    </section>

                    <div class="authorize-actions">
                        <form method="POST" action="{{ route('passport.authorizations.approve') }}">
                            @csrf
                            <input type="hidden" name="state" value="{{ $request->state }}">
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">
                            <button class="submit-button" type="submit">Autorizar acesso</button>
                        </form>
                        <form method="POST" action="{{ route('passport.authorizations.deny') }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="state" value="{{ $request->state }}">
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">
                            <button class="submit-button decline-button" type="submit">Recusar</button>
                        </form>
                    </div>

                    <p class="login-footer authorize-footer">Autorize apenas aplicativos em que você confia.<br>Sua senha não será compartilhada com o aplicativo.</p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
