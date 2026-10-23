<?php

use App\Mcp\Servers\EcommerceServer;
use App\Mcp\Tools\ListOrders;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

    $this->user = User::factory()->create();
    $this->pending = OrderStatus::query()->create(['name' => 'Pendente']);
    $this->paid = OrderStatus::query()->create(['name' => 'Pago']);

    $this->orders = collect([
        [$this->pending->id, 0, '2026-01-10 12:00:00'],
        [$this->paid->id, 100, '2026-01-15 12:00:00'],
        [$this->paid->id, 200, '2026-01-20 12:00:00'],
    ])->map(function (array $attributes) {
        $order = $this->user->orders()->make([
            'status_id' => $attributes[0],
            'total_amount' => $attributes[1],
        ]);
        $order->created_at = $attributes[2];
        $order->save();

        return $order->refresh()->load(['status', 'user']);
    });
});

it('lista apenas os pedidos do usuário autenticado com status e usuário', function () {
    $other = User::factory()->create();
    $other->orders()->create(['status_id' => $this->paid->id, 'total_amount' => 100]);

    EcommerceServer::actingAs($this->user)
        ->tool(ListOrders::class, ['user_id' => $other->id])
        ->assertOk()
        ->assertStructuredContent([
            'orders' => $this->orders->toArray(),
        ]);
});

it('retorna uma lista vazia para um usuário sem pedidos', function () {
    EcommerceServer::actingAs(User::factory()->create())
        ->tool(ListOrders::class)
        ->assertOk()
        ->assertStructuredContent(['orders' => []]);
});

it('filtra os pedidos pelo status', function () {
    EcommerceServer::actingAs($this->user)
        ->tool(ListOrders::class, ['status_id' => $this->paid->id])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->count('orders', 2)
            ->where('orders.0.id', $this->orders[1]->id)
            ->where('orders.1.id', $this->orders[2]->id));
});

it('aplica filtros de data e valor incluindo seus limites', function (array $filters, array $indices) {
    $expected = $this->orders->only($indices)->values();

    EcommerceServer::actingAs($this->user)
        ->tool(ListOrders::class, $filters)
        ->assertOk()
        ->assertStructuredContent(['orders' => $expected->toArray()]);
})->with([
    'data inicial' => [['after' => '2026-01-15 12:00:00'], [1, 2]],
    'data final' => [['before' => '2026-01-15 12:00:00'], [0, 1]],
    'valor máximo' => [['amount_lt' => 100], [0, 1]],
    'valor mínimo' => [['amount_gt' => 100], [1, 2]],
    'valor zero como string' => [['amount_lt' => '0'], [0]],
    'filtros combinados' => [[
        'after' => '2026-01-15 12:00:00',
        'before' => '2026-01-20 12:00:00',
        'amount_gt' => 50,
        'amount_lt' => 150,
    ], [1]],
    'sem correspondências' => [['amount_gt' => 201], []],
]);

it('não lista pedidos excluídos', function () {
    $this->orders[1]->delete();

    EcommerceServer::actingAs($this->user)
        ->tool(ListOrders::class)
        ->assertOk()
        ->assertStructuredContent([
            'orders' => $this->orders->only([0, 2])->values()->toArray(),
        ]);
});

it('retorna erros MCP para filtros inválidos', function (array $filters, string $field) {
    EcommerceServer::actingAs($this->user)
        ->tool(ListOrders::class, $filters)
        ->assertHasErrors([$field]);
})->with([
    'status inexistente' => [['status_id' => 999999], 'status'],
    'status inválido' => [['status_id' => 'abc'], 'status'],
    'data inicial inválida' => [['after' => 'inválida'], 'after'],
    'data final inválida' => [['before' => 'inválida'], 'before'],
    'valor máximo inválido' => [['amount_lt' => 'abc'], 'amount lt'],
    'valor mínimo inválido' => [['amount_gt' => 'abc'], 'amount gt'],
]);
