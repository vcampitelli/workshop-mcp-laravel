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
use App\Domains\Order\ViewOrder\ViewOrderUseCase;
use App\Domains\Product\ViewProduct\ViewProductUseCase;
use App\Domains\ProductCategory\ViewProductCategory\ViewProductCategoryUseCase;
use App\Domains\Review\ListProductReviews\ListProductReviewsUseCase;
use App\Domains\Review\ListProductReviews\ListProductReviewsUseCaseInput;
use App\Domains\Shared\UseCaseError;
use App\Mcp\Tools\ListOrders;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    User::factory()->create(['id' => 1]);
    OrderStatus::query()->create(['name' => 'Pendente']);
});

test('order use cases run without an HTTP request and preserve zero on partial updates', function () {
    $created = (new CreateOrderUseCase)(new CreateOrderUseCaseInput(1, 1, 50));
    expect($created->error)->toBeNull();
    $order = $created->order;
    expect($order)->toBeInstanceOf(Order::class);

    $updated = (new UpdateOrderUseCase)(new UpdateOrderUseCaseInput($order->id, totalAmount: 0));
    expect($updated->error)->toBeNull()
        ->and($updated->order->total_amount)->toBe(0.0)
        ->and($updated->order->user_id)->toBe(1)
        ->and($updated->order->status_id)->toBe(1);

    $list = (new ListOrdersUseCase)(new ListOrdersUseCaseInput(amountLt: 0));
    expect($list->orders->modelKeys())->toBe([$order->id]);

    $deleted = (new DeleteOrderUseCase)($order->id);
    expect($deleted->deleted)->toBeTrue();
    expect((new ViewOrderUseCase)($order->id)->error)->toBe(UseCaseError::NotFound);
    $this->assertSoftDeleted('orders', ['id' => $order->id]);
});

test('creating an order returns a domain error for invalid data without persisting', function (int $userId, int $statusId, float $amount) {
    $result = (new CreateOrderUseCase)(new CreateOrderUseCaseInput($userId, $statusId, $amount));
    expect($result->error)->toBe(UseCaseError::InvalidInput)->and($result->order)->toBeNull();
    $this->assertDatabaseCount('orders', 0);
})->with([[999, 1, 10.0], [1, 999, 10.0], [1, 1, -1.0], [1, 1, INF]]);

test('missing records are represented by outputs instead of HTTP exceptions', function () {
    foreach ([
        (new ViewOrderUseCase)(999),
        (new PrepareEditOrderUseCase)(999),
        (new UpdateOrderUseCase)(new UpdateOrderUseCaseInput(999, totalAmount: 10)),
        (new UpdateOrderStatusUseCase)(999, 1),
        (new DeleteOrderUseCase)(999),
        (new ViewProductUseCase)(999),
        (new ViewProductCategoryUseCase)(999),
        (new ListProductReviewsUseCase)(new ListProductReviewsUseCaseInput(999)),
    ] as $output) {
        expect($output->error)->toBe(UseCaseError::NotFound);
    }
});

test('invalid updates return domain errors and leave the order unchanged', function () {
    $order = Order::query()->create(['user_id' => 1, 'status_id' => 1, 'total_amount' => 50]);
    $result = (new UpdateOrderUseCase)(new UpdateOrderUseCaseInput($order->id, userId: 999, totalAmount: 100));
    $status = (new UpdateOrderStatusUseCase)($order->id, 999);
    expect($result->error)->toBe(UseCaseError::InvalidInput)
        ->and($status->error)->toBe(UseCaseError::InvalidInput)
        ->and($order->fresh()->total_amount)->toBe(50.0);
});

test('MCP and direct callers share the order use case and reject invalid filters', function () {
    Order::query()->create(['user_id' => 1, 'status_id' => 1, 'total_amount' => 0]);
    Order::query()->create(['user_id' => 1, 'status_id' => 1, 'total_amount' => 50]);
    $tool = app(ListOrders::class);
    $result = $tool->handle(new Request(['amount_lt' => 0]), new ListOrdersUseCase);
    expect($result->getStructuredContent()['orders'])->toHaveCount(1);

    expect(fn () => $tool->handle(new Request(['status_id' => 'invalid']), new ListOrdersUseCase))
        ->toThrow(ValidationException::class);
});
