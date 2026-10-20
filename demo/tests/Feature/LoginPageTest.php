<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
});

test('ecommerce login page renders', function () {
    $this->get('/login')->assertOk()
        ->assertSee('Entrar — Meu Ecommerce')
        ->assertSee('css/login.css')
        ->assertSee('Manter conectado');
});

test('ecommerce login remembers the user only when requested', function (bool $remember) {
    $user = User::factory()->create(['password' => 'secret']);
    $guard = Auth::guard('web');

    $response = $this->withSession(['url.intended' => '/oauth/authorize?client_id=demo'])
        ->post('/login', ['email' => $user->email, 'password' => 'secret', 'remember' => $remember ? '1' : '0'])
        ->assertRedirect('/oauth/authorize?client_id=demo');

    $this->assertAuthenticatedAs($user, 'web');

    if ($remember) {
        $response->assertCookie($guard->getRecallerName());
    } else {
        $response->assertCookieMissing($guard->getRecallerName());
    }
})->with([true, false]);

test('ecommerce login displays errors and retains the email', function () {
    $this->from('/login')->post('/login', ['email' => 'cliente@example.com', 'password' => 'invalid'])
        ->assertRedirect('/login')->assertSessionHasErrors('email');

    $this->view('auth.login', ['errors' => session('errors')])
        ->assertSee('E-mail ou senha inválidos.')
        ->assertSee('cliente@example.com')
        ->assertSee('role="alert"', false);
    $this->assertGuest('web');
});
