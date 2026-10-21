<?php

use App\Domains\Order\CreateOrder\CreateOrderUseCase;
use App\Domains\Order\CreateOrder\CreateOrderUseCaseInput;
use App\Domains\Order\DeleteOrder\DeleteOrderUseCase;
use App\Domains\Order\ListOrders\ListOrdersUseCase;
use App\Domains\Order\ListOrders\ListOrdersUseCaseInput;
use App\Domains\Order\PrepareEditOrder\PrepareEditOrderUseCase;
use App\Domains\Order\UpdateOrder\UpdateOrderUseCase;
use App\Domains\Order\UpdateOrder\UpdateOrderUseCaseInput;
use App\Domains\Order\UpdateOrderStatus\UpdateOrderStatusUseCase;
use App\Domains\Order\UpdateOrderStatus\UpdateOrderStatusUseCaseInput;
use App\Domains\Order\ViewOrder\ViewOrderUseCase;
use App\Domains\Shared\UseCaseError;
use App\Mcp\Servers\OrdersServer;
use App\Mcp\Tools\ListOrders;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Passport\Passport;
use League\OAuth2\Server\ResourceServer;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    $this->app->instance(ResourceServer::class, Mockery::mock(ResourceServer::class));
    $this->owner = User::factory()->create();
    $this->other = User::factory()->create();
    $this->status = OrderStatus::query()->create(['name' => 'Pendente']);
    $this->ownOrder = $this->owner->orders()->create(['status_id' => $this->status->id, 'total_amount' => 50]);
    $this->otherOrder = $this->other->orders()->create(['status_id' => $this->status->id, 'total_amount' => 50]);
    Order::query()->create(['user_id' => null, 'status_id' => $this->status->id, 'total_amount' => 50]);
});

test('use cases restrict every order operation to the supplied user without HTTP authentication', function () {
    $input = ListOrdersUseCaseInput::fromArray(['user_id' => $this->other->id]);
    $output = (new ListOrdersUseCase)($input, $this->owner);
    expect($output->orders->modelKeys())->toBe([$this->ownOrder->id]);
    expect((new ListOrdersUseCase)($input, $this->other)->orders->modelKeys())->toBe([$this->otherOrder->id]);

    foreach ([
        (new ViewOrderUseCase)($this->otherOrder->id, $this->owner),
        (new PrepareEditOrderUseCase)($this->otherOrder->id, $this->owner),
        (new UpdateOrderUseCase)(new UpdateOrderUseCaseInput($this->otherOrder->id, totalAmount: 0), $this->owner),
        (new UpdateOrderStatusUseCase)(new UpdateOrderStatusUseCaseInput($this->otherOrder->id, $this->status->id), $this->owner),
        (new DeleteOrderUseCase)($this->otherOrder->id, $this->owner),
    ] as $result) {
        expect($result->error)->toBe(UseCaseError::NotFound);
    }
    expect($this->otherOrder->fresh()->total_amount)->toBe(50.0);
    $this->assertNotSoftDeleted($this->otherOrder);
    expect((new ViewOrderUseCase)($this->ownOrder->id, $this->owner)->order->id)->toBe($this->ownOrder->id);
    expect((new DeleteOrderUseCase)($this->ownOrder->id, $this->owner)->deleted)->toBeTrue();
});

test('creation and updates cannot accept ownership from input data', function () {
    $input = CreateOrderUseCaseInput::fromArray([
        'user_id' => $this->other->id, 'status_id' => $this->status->id, 'total_amount' => 10,
    ]);
    $created = (new CreateOrderUseCase)($input, $this->owner);
    expect($created->order->user_id)->toBe($this->owner->id);
    $update = UpdateOrderUseCaseInput::fromArray([
        'order_id' => $created->order->id, 'user_id' => $this->other->id, 'total_amount' => 0,
    ]);
    $updated = (new UpdateOrderUseCase)($update, $this->owner);
    expect($updated->order->user_id)->toBe($this->owner->id)
        ->and($updated->order->total_amount)->toBe(0.0);
});

test('API hides foreign orders and derives ownership from the authenticated user', function () {
    $this->app->instance(ResourceServer::class, Mockery::mock(ResourceServer::class));
    Passport::actingAs($this->owner, array_keys(config('permissions')));
    $this->getJson('/orders?user_id='.$this->other->id)->assertOk()->assertJsonCount(1)
        ->assertJsonPath('0.id', $this->ownOrder->id);
    $id = $this->otherOrder->id;
    foreach ([['GET', "/orders/$id", []], ['GET', "/orders/$id/edit", []],
        ['DELETE', "/orders/$id", []], ['PATCH', "/orders/$id", ['total_amount' => 0]],
        ['PATCH', "/orders/$id/status", ['status_id' => $this->status->id]],
    ] as [$method, $url, $data]) {
        $this->json($method, $url, $data)->assertNotFound();
    }
    foreach ([[], ['user_id' => $this->other->id]] as $extra) {
        $this->postJson('/orders', [...$extra, 'status_id' => $this->status->id, 'total_amount' => 10])
            ->assertCreated()->assertJsonPath('user_id', $this->owner->id);
    }
    $this->patchJson('/orders/'.$this->ownOrder->id, ['user_id' => $this->other->id])
        ->assertOk()->assertJsonPath('user_id', $this->owner->id);
    $this->assertNotSoftDeleted($this->otherOrder);
});

test('MCP lists only the authenticated users orders and omits the user filter from its schema', function () {
    Passport::actingAs($this->owner, ['orders:list']);
    $tool = app(ListOrders::class);
    $content = $tool->handle(new Request(['user_id' => $this->other->id]), new ListOrdersUseCase)->getStructuredContent();
    expect(array_column($content['orders'], 'id'))->toBe([$this->ownOrder->id])
        ->and($tool->toArray()['inputSchema']['properties'])->not->toHaveKey('user_id');
    OrdersServer::tool(ListOrders::class, ['user_id' => $this->other->id])->assertStructuredContent($content);
});

test('MCP rejects unauthenticated calls even with a supplied user id', function () {
    $this->postJson('/mcp/orders', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call', 'params' => ['name' => 'list-orders']])->assertUnauthorized();
    expect(fn () => app(ListOrders::class)->handle(new Request(['user_id' => $this->owner->id]), new ListOrdersUseCase))
        ->toThrow(HttpException::class);
});
