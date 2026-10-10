<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Workshop MCP</title>
</head>
<body>
    <h1>Entrar</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <p><label>E-mail <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required></label></p>
        <p><label>Senha <input type="password" name="password" autocomplete="current-password" required></label></p>
        @foreach ($errors->all() as $error)
            <p role="alert">{{ $error }}</p>
        @endforeach
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
