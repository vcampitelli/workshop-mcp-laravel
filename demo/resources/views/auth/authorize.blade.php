<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autorizar acesso — Workshop MCP</title>
</head>
<body>
    <h1>Autorizar {{ $client->name }}</h1>
    <p>Conectado como {{ $user->name }}. Este aplicativo solicita as permissões:</p>
    <ul>
        @forelse ($scopes as $scope)
            <li>{{ $scope->description }} ({{ $scope->id }})</li>
        @empty
            <li>Nenhuma permissão solicitada.</li>
        @endforelse
    </ul>
    <form method="POST" action="{{ route('passport.authorizations.approve') }}">
        @csrf
        <input type="hidden" name="state" value="{{ $request->state }}">
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <button type="submit">Autorizar</button>
    </form>
    <form method="POST" action="{{ route('passport.authorizations.deny') }}">
        @csrf
        @method('DELETE')
        <input type="hidden" name="state" value="{{ $request->state }}">
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <button type="submit">Recusar</button>
    </form>
</body>
</html>
