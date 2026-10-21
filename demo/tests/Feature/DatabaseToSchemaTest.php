<?php

use App\Domains\Order\ListOrders\ListOrdersUseCase;
use App\Mcp\Mappers\DatabaseToSchema;
use App\Mcp\Tools\ListOrders;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Laravel\Mcp\Request;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
});

test('order database schema contains columns and requested relations when serialized', function () {
    $schema = (new DatabaseToSchema)->mapModel(new Order, [
        'status' => new OrderStatus,
        'user' => new User,
    ])->toArray();

    expect($schema['properties'])->toHaveKeys(['id', 'user_id', 'status_id', 'total_amount', 'status', 'user'])
        ->and($schema['properties']['user']['properties'])->toHaveKey('email')
        ->not->toHaveKeys(['password', 'remember_token']);
});

test('order tool publishes the database schema as MCP output metadata', function () {
    $metadata = app(ListOrders::class)->toArray();
    expect($metadata)->toHaveKey('outputSchema');
    $item = $metadata['outputSchema']['properties']['orders']['items'];
    expect($item['type'])->toBe('object')
        ->and($item['properties'])->toHaveKeys(['id', 'total_amount', 'status', 'user']);
});

test('order relationship schema accepts the types actually returned by the tool', function (string $scenario) {
    $user = User::factory()->create();
    $status = OrderStatus::query()->create(['name' => 'Pendente']);
    Order::query()->create([
        'user_id' => $user->id,
        'status_id' => $scenario === 'missing' ? null : $status->id,
        'total_amount' => 50,
    ]);

    if ($scenario === 'deleted user') {
        $user->delete();
    }

    $this->actingAs($user);
    $tool = app(ListOrders::class);
    $content = $tool->handle(new Request([]), new ListOrdersUseCase)->getStructuredContent();
    $properties = $tool->toArray()['outputSchema']['properties']['orders']['items']['properties'];

    expect($content['orders'])->toHaveCount(1);
    $order = $content['orders'][0];
    expect($order['user'] === null)->toBe($scenario === 'deleted user')
        ->and($order['status'] === null)->toBe($scenario === 'missing');

    foreach (['user', 'status'] as $relation) {
        expect($properties[$relation]['type'])->toBe(['object', 'null'])
            ->toContain($order[$relation] === null ? 'null' : 'object');
    }

    if ($scenario === 'present') {
        expect($order['user'])->toHaveKey('email')->not->toHaveKeys(['password', 'remember_token']);
    }
})->with(['present', 'missing', 'deleted user']);

test('reusing the mapper does not discard relations requested after a plain schema', function () {
    $mapper = new DatabaseToSchema;
    $mapper->mapModel(new Order);
    $schema = $mapper->mapModel(new Order, ['status' => new OrderStatus])->toArray();
    expect($schema['properties'])->toHaveKey('status');
});

test('reusing the mapper respects each model hidden fields and isolates returned schemas', function () {
    $mapper = new DatabaseToSchema;
    $visible = $mapper->mapModel(new Order);
    $visible->description('First caller');
    $hidden = $mapper->mapModel((new Order)->setHidden(['total_amount']))->toArray();
    expect($hidden['properties'])->not->toHaveKey('total_amount');
    expect($hidden)->not->toHaveKey('description');
    expect($mapper->mapModel(new Order)->toArray()['properties'])->toHaveKey('total_amount');
});
