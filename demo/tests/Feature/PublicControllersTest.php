<?php

use Illuminate\Support\Facades\DB;

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
