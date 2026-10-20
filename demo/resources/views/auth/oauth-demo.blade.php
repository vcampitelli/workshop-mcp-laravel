<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Resultado OAuth2 — Meu Ecommerce</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .demo-result { width: min(760px, 100%); padding: 36px; border-radius: 14px; background: white; }
        .demo-result h1 { margin-top: 0; font-size: 26px; }
        .demo-result p { line-height: 1.6; }
        .demo-result textarea { display: block; width: 100%; min-height: 200px; margin: 12px 0 24px; padding: 12px; resize: vertical; overflow-wrap: anywhere; }
        .demo-result pre { padding: 16px; overflow: auto; background: #f5f5f8; border-radius: 6px; }
    </style>
</head>
<body>
    <main class="login-page">
        <section class="demo-result">
            @if ($error)
                <h1>Resultado da autorização</h1>
                <p role="status">{{ $error }}</p>
            @else
                <h1>Conexão autorizada!</h1>
                <p>O Passport validou o código e o PKCE e emitiu um token real para o Meu Ecommerce.</p>
                <p>Tipo: {{ $token['token_type'] }}. Expira em {{ $token['expires_in'] }} segundos.</p>
                <label for="access-token">Token de acesso</label>
                <textarea id="access-token" readonly spellcheck="false">{{ $token['access_token'] }}</textarea>
                <p>Para consultar os produtos, substitua <code>SEU_TOKEN</code> pelo token acima:</p>
                <pre><code>curl {{ url('/products') }} \
  -H "Accept: application/json" \
  -H "Authorization: Bearer SEU_TOKEN"</code></pre>
            @endif
            <p>Para iniciar outra demonstração, execute <code>php artisan oauth:demo</code>.</p>
        </section>
    </main>
</body>
</html>
