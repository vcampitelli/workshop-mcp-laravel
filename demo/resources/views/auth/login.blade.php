<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ededf0">
    <title>Entrar — Meu Ecommerce</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <main class="login-page">
        <div class="login-card">
            <aside class="login-story" aria-label="Meu Ecommerce">
                <div class="brand">
                    <svg viewBox="0 0 28 28" fill="none" aria-hidden="true">
                        <rect x="1" y="1" width="11" height="11" rx="5.5" fill="currentColor"/>
                        <rect x="16" y="1" width="11" height="11" rx="5.5" fill="currentColor"/>
                        <rect x="1" y="16" width="11" height="11" rx="5.5" fill="currentColor"/>
                        <rect x="16" y="16" width="11" height="11" rx="5.5" fill="currentColor"/>
                    </svg>
                    <span>Meu Ecommerce</span>
                </div>
                <div class="story-copy">
                    <h2>Sua loja, suas ideias.<br>Tudo em um só lugar.</h2>
                    <p>Conecte produtos, pedidos e novas possibilidades para o seu negócio.</p>
                </div>
            </aside>

            <section class="login-main" aria-labelledby="login-title">
                <div class="login-content">
                    <header class="login-heading">
                        <h1 id="login-title">Que bom ter você de volta!</h1>
                        <p>Entre na sua conta para gerenciar<br>o seu ecommerce.</p>
                    </header>

                    <form class="login-form" method="POST" action="{{ route('login') }}">
                        @csrf

                        @if ($errors->any())
                            <div class="form-errors" id="login-errors" role="alert">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <div class="field">
                            <label for="email">E-mail</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                placeholder="voce@exemplo.com" autocomplete="username" required
                                @error('email') aria-invalid="true" aria-describedby="login-errors" @enderror>
                        </div>

                        <div class="field">
                            <label for="password">Senha</label>
                            <input id="password" type="password" name="password"
                                placeholder="Digite sua senha" autocomplete="current-password" required
                                @error('password') aria-invalid="true" aria-describedby="login-errors" @enderror>
                        </div>

                        <label class="remember" for="remember">
                            <span>Manter conectado</span>
                            <input id="remember" type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        </label>

                        <button class="submit-button" type="submit">Entrar</button>
                    </form>

                    <div class="login-note">Meu Ecommerce</div>
                    <p class="login-footer">Seu negócio começa com uma conexão.</p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
