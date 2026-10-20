<?php

use App\Domains\Auth\AuthenticateUser\AuthenticateUserUseCaseInput;
use App\Domains\OAuth\CompleteOAuthDemo\CompleteOAuthDemoUseCaseInput;
use App\Domains\Order\CreateOrder\CreateOrderUseCaseInput;
use App\Domains\Order\ListOrders\ListOrdersUseCaseInput;
use App\Domains\Order\UpdateOrder\UpdateOrderUseCaseInput;
use App\Domains\Order\UpdateOrderStatus\UpdateOrderStatusUseCaseInput;
use App\Domains\Product\ListProducts\ListProductsUseCaseInput;
use App\Domains\ProductCategory\ListProductCategories\ListProductCategoriesUseCaseInput;
use App\Domains\Review\ListProductReviews\ListProductReviewsUseCaseInput;
use App\Mcp\Prompts\ListOrders as ListOrdersPrompt;
use App\Mcp\Servers\OrdersServer;
use App\Mcp\Tools\ListOrders as ListOrdersTool;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    User::factory()->create(['id' => 1]);
    OrderStatus::query()->create(['name' => 'Pendente']);
});

test('input factories convert numeric strings and preserve zeros and omitted fields', function () {
    $created = CreateOrderUseCaseInput::fromArray([
        'user_id' => '1', 'status_id' => '1', 'total_amount' => '0', 'unexpected' => 'ignored',
    ]);
    expect($created->userId)->toBe(1)
        ->and($created->statusId)->toBe(1)
        ->and($created->totalAmount)->toBe(0.0);

    $updated = UpdateOrderUseCaseInput::fromArray(['order_id' => '42', 'total_amount' => '0']);
    expect($updated->orderId)->toBe(42)
        ->and($updated->totalAmount)->toBe(0.0)
        ->and($updated->userId)->toBeNull()
        ->and($updated->statusId)->toBeNull();

    $filters = ListOrdersUseCaseInput::fromArray(['status_id' => '1', 'amount_lt' => '0']);
    expect($filters->statusId)->toBe(1)
        ->and($filters->amountLt)->toBe(0.0)
        ->and($filters->amountGt)->toBeNull();

    expect(ListProductsUseCaseInput::fromArray(['price_gt' => '0'])->priceGt)->toBe(0.0)
        ->and(ListProductCategoriesUseCaseInput::fromArray([])->parentId)->toBeNull()
        ->and(ListProductReviewsUseCaseInput::fromArray(['product_id' => '42', 'rating' => '5'])->rating)->toBe(5)
        ->and(UpdateOrderStatusUseCaseInput::fromArray(['order_id' => '42', 'status_id' => '1'])->statusId)->toBe(1);
});

test('login input defaults remember to false and converts checkbox values', function () {
    $credentials = ['email' => 'user@example.com', 'password' => 'password'];
    expect(AuthenticateUserUseCaseInput::fromArray($credentials)->remember)->toBeFalse()
        ->and(AuthenticateUserUseCaseInput::fromArray([...$credentials, 'remember' => '0'])->remember)->toBeFalse()
        ->and(AuthenticateUserUseCaseInput::fromArray([...$credentials, 'remember' => '1'])->remember)->toBeTrue();
});

test('invalid arrays produce Laravel validation errors before constructing an input', function (string $inputClass, array $data, array $fields) {
    try {
        $inputClass::fromArray($data);
        $this->fail('The input factory should reject invalid data.');
    } catch (ValidationException $exception) {
        expect($exception->status)->toBe(422)
            ->and(array_keys($exception->errors()))->toBe($fields);
    }
})->with([
    'create' => [CreateOrderUseCaseInput::class, ['user_id' => 999, 'status_id' => 1, 'total_amount' => -1], ['user_id', 'total_amount']],
    'partial update' => [UpdateOrderUseCaseInput::class, ['order_id' => 42, 'total_amount' => null], ['total_amount']],
    'order filters' => [ListOrdersUseCaseInput::class, ['after' => 'invalid date', 'amount_lt' => []], ['after', 'amount_lt']],
    'order status' => [UpdateOrderStatusUseCaseInput::class, ['order_id' => 42], ['status_id']],
    'product filters' => [ListProductsUseCaseInput::class, ['price_lt' => -1], ['price_lt']],
    'category filters' => [ListProductCategoriesUseCaseInput::class, ['parent_id' => 999], ['parent_id']],
    'review filters' => [ListProductReviewsUseCaseInput::class, ['product_id' => 42, 'rating' => 6], ['rating']],
    'login' => [AuthenticateUserUseCaseInput::class, ['email' => 'invalid', 'password' => 'password', 'remember' => 'invalid'], ['email', 'remember']],
    'oauth context' => [CompleteOAuthDemoUseCaseInput::class, ['session' => 'invalid'], ['session']],
]);

test('MCP tool and prompt translate input validation exceptions through their native handlers', function () {
    OrdersServer::tool(ListOrdersTool::class, ['amount_lt' => 'invalid'])
        ->assertHasErrors(['The amount lt field must be a number.']);
    OrdersServer::prompt(ListOrdersPrompt::class, ['after' => 'invalid'])
        ->assertHasErrors(['after']);
});

test('MCP prompt retains a zero filter after constructing its input', function () {
    OrdersServer::prompt(ListOrdersPrompt::class, ['amount_lt' => '0'])
        ->assertSee('com preço menor que R$0');
});
