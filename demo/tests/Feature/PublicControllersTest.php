<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use League\OAuth2\Server\ResourceServer;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

    DB::table('users')->insert(['id' => 1, 'name' => 'Cliente', 'email' => 'cliente@example.com', 'password' => 'password']);
    DB::table('order_statuses')->insert(['id' => 1, 'name' => 'Pendente']);
    DB::table('order_statuses')->insert(['id' => 2, 'name' => 'Concluído']);
    DB::table('product_categories')->insert(['id' => 1, 'name' => 'Livros']);
    DB::table('product_statuses')->insert(['id' => 1, 'name' => 'Disponível']);
    DB::table('products')->insert([
        'id' => 1, 'product_category_id' => 1, 'product_status_id' => 1,
        'name' => 'Livro de PHP', 'description' => 'Exemplo', 'price' => 50, 'quantity' => 10,
    ]);

    // The fake Passport token avoids signing JWTs in controller tests.
    $this->app->instance(ResourceServer::class, Mockery::mock(ResourceServer::class));
    Passport::actingAs(User::findOrFail(1), array_keys(config('permissions')));
});

test('protected routes reject requests without a token', function () {
    Auth::forgetGuards();
    Auth::shouldUse('api');

    foreach ([
        ['GET', '/orders'],
        ['GET', '/orders/create'],
        ['POST', '/orders'],
        ['GET', '/orders/1'],
        ['GET', '/orders/1/edit'],
        ['PUT', '/orders/1'],
        ['PATCH', '/orders/1'],
        ['DELETE', '/orders/1'],
        ['PATCH', '/orders/1/status'],
    ] as [$method, $uri]) {
        $this->json($method, $uri)->assertUnauthorized();
    }
    $this->getJson('/products')->assertUnauthorized();
    $this->getJson('/product-categories')->assertUnauthorized();
    $this->getJson('/products/1/reviews')->assertUnauthorized();
});

test('login preserves the intended OAuth destination', function () {
    User::findOrFail(1)->update(['password' => 'secret']);

    $this->withSession(['url.intended' => '/oauth/authorize?client_id=demo'])
        ->post('/login', ['email' => 'cliente@example.com', 'password' => 'secret'])
        ->assertRedirect('/oauth/authorize?client_id=demo');
    $this->assertAuthenticatedAs(User::findOrFail(1), 'web');

    $this->post('/logout')->assertRedirect('/login');
    $this->assertGuest('web');
});

test('login rejects invalid credentials', function () {
    $this->postJson('/login', ['email' => 'cliente@example.com', 'password' => 'wrong'])
        ->assertUnprocessable()->assertJsonValidationErrors('email');
    $this->assertGuest('web');
});

test('each operation requires its own scope', function (string $method, string $uri, string $scope, array $data, int $status) {
    DB::table('orders')->insert(['id' => 1, 'user_id' => 1, 'status_id' => 1, 'total_amount' => 50]);
    $user = User::findOrFail(1);

    Passport::actingAs($user, ['unrelated:scope']);
    $this->json($method, $uri, $data)->assertForbidden();

    Passport::actingAs($user, [$scope]);
    $this->json($method, $uri, $data)->assertStatus($status);
})->with([
    ['GET', '/orders', 'orders:list', [], 200],
    ['GET', '/orders/1', 'orders:show', [], 200],
    ['GET', '/orders/create', 'orders:create', [], 200],
    ['POST', '/orders', 'orders:create', ['user_id' => 1, 'status_id' => 1, 'total_amount' => 50], 201],
    ['GET', '/orders/1/edit', 'orders:update', [], 200],
    ['PATCH', '/orders/1', 'orders:update', ['total_amount' => 25], 200],
    ['PUT', '/orders/1', 'orders:update', ['total_amount' => 25], 200],
    ['DELETE', '/orders/1', 'orders:delete', [], 204],
    ['PATCH', '/orders/1/status', 'orders:update-status', ['status_id' => 2], 200],
    ['GET', '/products', 'products:list', [], 200],
    ['GET', '/products/1', 'products:show', [], 200],
    ['GET', '/product-categories', 'product-categories:list', [], 200],
    ['GET', '/product-categories/1', 'product-categories:show', [], 200],
    ['GET', '/products/1/reviews', 'reviews:list', [], 200],
]);

test('list permission does not allow creating or deleting orders', function () {
    DB::table('orders')->insert(['id' => 1, 'user_id' => 1, 'status_id' => 1, 'total_amount' => 50]);
    Passport::actingAs(User::findOrFail(1), ['orders:list']);

    $this->getJson('/orders')->assertOk();
    $this->postJson('/orders', ['user_id' => 1, 'status_id' => 1, 'total_amount' => 50])->assertForbidden();
    $this->deleteJson('/orders/1')->assertForbidden();
    $this->assertDatabaseHas('orders', ['id' => 1, 'deleted_at' => null]);
});

test('orders can be created, updated and deleted', function () {
    $response = $this->postJson('/orders', ['user_id' => 1, 'status_id' => 1, 'total_amount' => 50])
        ->assertCreated()->assertJsonPath('status.id', 1);
    $id = $response->json('id');

    $this->getJson("/orders/{$id}")->assertOk()->assertJsonPath('user.id', 1);
    $this->getJson('/orders/create')->assertOk()->assertJsonCount(2, 'statuses');
    $this->getJson("/orders/{$id}/edit")->assertOk()->assertJsonPath('order.id', $id);
    $this->patchJson("/orders/{$id}", ['total_amount' => 0])
        ->assertOk()->assertJsonPath('total_amount', 0);
    $this->getJson('/orders?amount_lt=0')->assertOk()->assertJsonCount(1);
    $this->patchJson("/orders/{$id}/status", ['status_id' => 2])
        ->assertOk()->assertJsonPath('status.id', 2);
    $this->deleteJson("/orders/{$id}")->assertNoContent();
    $this->getJson("/orders/{$id}")->assertNotFound();
});

test('invalid order data is rejected', function () {
    $this->postJson('/orders', ['user_id' => 999, 'status_id' => 999, 'total_amount' => -1])
        ->assertUnprocessable()->assertJsonValidationErrors(['user_id', 'status_id', 'total_amount']);

    $id = DB::table('orders')->insertGetId(['user_id' => 1, 'status_id' => 1, 'total_amount' => 50]);
    $this->patchJson("/orders/{$id}", ['total_amount' => null])
        ->assertUnprocessable()->assertJsonValidationErrors('total_amount');
    $this->patchJson("/orders/{$id}/status", [])
        ->assertUnprocessable()->assertJsonValidationErrors('status_id');
});

test('products and categories can be browsed and filtered', function () {
    $this->getJson('/products?product_category_id=1&name=PHP')->assertOk()->assertJsonCount(1);
    $this->getJson('/products?price_lt=0')->assertOk()->assertJsonCount(0);
    $this->getJson('/products/1')->assertOk()->assertJsonPath('category.name', 'Livros');
    $this->getJson('/product-categories?name=Livros')->assertOk()->assertJsonCount(1);
    $this->getJson('/product-categories/1')->assertOk()->assertJsonCount(1, 'products');
    $this->getJson('/products?product_category_id=999')
        ->assertUnprocessable()->assertJsonValidationErrors('product_category_id');
});

test('reviews are scoped to the requested product', function () {
    DB::table('products')->insert([
        'id' => 2, 'product_category_id' => 1, 'product_status_id' => 1,
        'name' => 'Outro livro', 'description' => 'Exemplo', 'price' => 20, 'quantity' => 5,
    ]);
    DB::table('reviews')->insert([
        ['product_id' => 1, 'user_id' => 1, 'rating' => 5, 'comment' => 'Bom'],
        ['product_id' => 2, 'user_id' => 1, 'rating' => 5, 'comment' => 'Outro produto'],
    ]);

    $this->getJson('/products/1/reviews?rating=5')->assertOk()
        ->assertJsonCount(1)->assertJsonPath('0.product_id', 1)->assertJsonPath('0.user.id', 1);
    $this->getJson('/products/1/reviews?rating=6')
        ->assertUnprocessable()->assertJsonValidationErrors('rating');
    $this->getJson('/products/999/reviews')->assertNotFound();
});
