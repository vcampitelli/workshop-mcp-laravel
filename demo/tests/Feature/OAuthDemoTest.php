<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\Passport;

beforeAll(function () {
    $directory = sys_get_temp_dir().'/ecommerce-oauth-test-'.bin2hex(random_bytes(8));
    mkdir($directory, 0700);
    $key = openssl_pkey_new(['private_key_bits' => 2048]);
    openssl_pkey_export($key, $private);
    file_put_contents($directory.'/oauth-private.key', $private);
    file_put_contents($directory.'/oauth-public.key', openssl_pkey_get_details($key)['key']);
    chmod($directory.'/oauth-private.key', 0600);
    chmod($directory.'/oauth-public.key', 0600);
    $GLOBALS['oauthDemoTestKeys'] = $directory;
});

afterAll(function () {
    $directory = $GLOBALS['oauthDemoTestKeys'];
    unlink($directory.'/oauth-private.key');
    unlink($directory.'/oauth-public.key');
    rmdir($directory);
    unset($GLOBALS['oauthDemoTestKeys']);
});

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    Passport::loadKeysFrom($GLOBALS['oauthDemoTestKeys']);
    $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
});

afterEach(function () {
    Passport::loadKeysFrom(storage_path());
});

function startOAuthDemo($test): string
{
    Artisan::call('oauth:demo');
    preg_match('~https?://[^\s]+/demo/oauth/start\?[^\s]+~', Artisan::output(), $matches);
    expect($matches)->not->toBeEmpty();

    return $test->get($matches[0])->assertRedirect()->headers->get('Location');
}

test('demo command creates and reuses a public client', function () {
    startOAuthDemo($this);
    startOAuthDemo($this);
    expect(Passport::client()->newQuery()->count())->toBe(1);
    expect(Passport::client()->newQuery()->first()->confidential())->toBeFalse();
});

test('demo rejects unknown permissions before creating a client', function () {
    $this->artisan('oauth:demo', ['--scope' => ['invalid']])->assertFailed();
    expect(Passport::client()->newQuery()->count())->toBe(0);
});

test('demo links require a signature and cannot be reused', function () {
    $this->get('/demo/oauth/start?ticket=invalid')->assertForbidden();
    Artisan::call('oauth:demo');
    preg_match('~https?://[^\s]+/demo/oauth/start\?[^\s]+~', Artisan::output(), $matches);
    $this->get($matches[0])->assertRedirect();
    $this->get($matches[0])->assertGone();
});

test('demo rejects callback state mismatches', function () {
    startOAuthDemo($this);
    $this->get('/demo/oauth/callback?state=wrong&code=wrong')->assertStatus(419);
    expect(session('oauth_demo'))->not->toBeNull();
});

test('demo completes a real passport authorization and issues a usable token', function () {
    $authorize = startOAuthDemo($this);
    parse_str(parse_url($authorize, PHP_URL_QUERY), $query);
    expect($query['code_challenge_method'])->toBe('S256');
    expect($query['prompt'])->toBe('consent');
    expect($query['scope'])->toBe('orders:list orders:show');

    $user = User::factory()->create(['password' => 'secret']);
    $this->get($authorize)->assertRedirect('/login');
    $login = $this->post('/login', ['email' => $user->email, 'password' => 'secret'])->assertRedirect();
    parse_str(parse_url($login->headers->get('Location'), PHP_URL_QUERY), $redirectQuery);
    expect($redirectQuery)->toEqual($query);
    $this->get($authorize)->assertOk()->assertSee('Assistente de Compras (demo)');
    $response = $this->post('/oauth/authorize', [
        'state' => $query['state'],
        'client_id' => $query['client_id'],
        'auth_token' => session('authToken'),
    ])->assertRedirect();

    $callback = $response->headers->get('Location');
    $response = $this->get($callback)->assertOk()->assertSee('Conexão autorizada!')->assertHeader('Cache-Control', 'no-store, private');
    preg_match('~<textarea[^>]*>([^<]+)</textarea>~', $response->getContent(), $matches);
    $this->withToken(html_entity_decode($matches[1]))->getJson('/orders')->assertOk();
    $this->get($callback)->assertStatus(419);
});

test('demo handles a real passport refusal without issuing tokens', function () {
    $authorize = startOAuthDemo($this);
    parse_str(parse_url($authorize, PHP_URL_QUERY), $query);
    $this->actingAs(User::factory()->create(), 'web')->get($authorize)->assertOk();
    $response = $this->delete('/oauth/authorize', [
        'state' => $query['state'],
        'client_id' => $query['client_id'],
        'auth_token' => session('authToken'),
    ])->assertRedirect();
    $this->get($response->headers->get('Location'))->assertOk()->assertSee('Você recusou o acesso.');
    $this->assertDatabaseCount('oauth_access_tokens', 0);
});

test('demo is unavailable in production', function () {
    $this->app->detectEnvironment(fn () => 'production');
    $this->artisan('oauth:demo')->assertFailed();
    $this->get(URL::signedRoute('oauth.demo.start', ['ticket' => 'anything']))->assertNotFound();
    $this->get('/demo/oauth/callback')->assertNotFound();
});
