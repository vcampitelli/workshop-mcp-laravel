<?php

use App\Domains\Auth\AuthenticateUser\AuthenticateUserUseCaseInput;
use App\Domains\Order\CreateOrder\CreateOrderUseCaseInput;
use App\Domains\Order\ListOrders\ListOrdersUseCaseInput;
use App\Domains\Order\UpdateOrder\UpdateOrderUseCaseInput;
use App\Domains\Order\UpdateOrderStatus\UpdateOrderStatusUseCaseInput;
use App\Domains\Product\ListProducts\ListProductsUseCaseInput;
use App\Domains\ProductCategory\ListProductCategories\ListProductCategoriesUseCaseInput;
use App\Domains\Review\ListProductReviews\ListProductReviewsUseCaseInput;
use App\Mcp\Mappers\ValidationToSchema;
use App\Rules\TitleAttribute;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;

function generatedValidationSchema(array $rules, array $overrides = []): array
{
    $object = (new JsonSchemaTypeFactory)->object((new ValidationToSchema)->map($rules, $overrides));

    return json_decode($object->toString(), true, flags: JSON_THROW_ON_ERROR);
}

test('serialized create order schema declares required integer identifiers and nonnegative totals', function () {
    $json = generatedValidationSchema(CreateOrderUseCaseInput::rules());

    expect($json)->toEqual([
        'type' => 'object',
        'required' => ['status_id', 'total_amount'],
        'properties' => [
            'status_id' => ['type' => 'integer', 'description' => 'ID do status do pedido'],
            'total_amount' => ['type' => 'number', 'minimum' => 0, 'description' => 'Valor total do pedido'],
        ],
    ]);
});

test('serialized order filters stay optional and dates do not claim a narrower date time format', function () {
    $json = generatedValidationSchema(ListOrdersUseCaseInput::rules());

    expect($json)->not->toHaveKey('required')
        ->and($json['properties']['status_id'])->toEqual(['type' => 'integer', 'description' => 'ID do status do pedido'])
        ->and($json['properties']['after'])->toEqual(['type' => 'string', 'description' => 'Pedidos depois dessa data'])
        ->and($json['properties']['before'])->not->toHaveKey('format')
        ->and($json['properties']['amount_lt']['type'])->toBe('number');
});

test('serialized review and catalog schemas retain their numeric and string limits', function () {
    $reviews = generatedValidationSchema(ListProductReviewsUseCaseInput::rules());
    expect($reviews['required'])->toBe(['product_id'])
        ->and($reviews['properties']['product_id'])->toEqual(['type' => 'integer', 'minimum' => 1])
        ->and($reviews['properties']['rating'])->toEqual([
            'type' => 'integer', 'minimum' => 1, 'maximum' => 5, 'description' => 'Nota da avaliação',
        ]);

    $products = generatedValidationSchema(ListProductsUseCaseInput::rules());
    $categories = generatedValidationSchema(ListProductCategoriesUseCaseInput::rules());
    expect($products['properties']['name']['maxLength'])->toBe(255)
        ->and($products['properties']['price_lt']['minimum'])->toBe(0)
        ->and($products['properties']['product_status_id']['type'])->toBe('integer')
        ->and($categories['properties']['name']['maxLength'])->toBe(255)
        ->and($categories['properties']['parent_id']['type'])->toBe('integer');
});

test('serialized login schema declares email and boolean types with presence on the parent object', function () {
    $json = generatedValidationSchema(AuthenticateUserUseCaseInput::rules());

    expect($json['required'])->toBe(['email', 'password'])
        ->and($json['properties']['email'])->toEqual([
            'type' => 'string', 'format' => 'email', 'minLength' => 1, 'description' => 'E-mail do usuário',
        ])
        ->and($json['properties']['password']['minLength'])->toBe(1)
        ->and($json['properties']['remember'])->toBe(['type' => 'boolean'])
        ->and($json['properties']['email'])->not->toHaveKey('required');
});

test('partial updates do not require fields marked sometimes and required', function () {
    $json = generatedValidationSchema(UpdateOrderUseCaseInput::rules());
    $status = generatedValidationSchema(UpdateOrderStatusUseCaseInput::rules());

    expect($json['required'])->toBe(['order_id'])
        ->and($json['properties']['total_amount'])->toEqual([
            'type' => 'number', 'minimum' => 0, 'description' => 'Valor total do pedido',
        ])
        ->and($status['required'])->toBe(['order_id', 'status_id']);
});

test('nullable and present are independent from optional fields and required rejects null', function () {
    $json = generatedValidationSchema([
        'optional' => 'nullable|integer',
        'present' => 'present|nullable|string',
        'required' => 'required|nullable|integer',
        'conditional' => 'sometimes|required|nullable|string',
    ]);

    expect($json['required'])->toBe(['present', 'required'])
        ->and($json['properties']['optional']['type'])->toBe(['integer', 'null'])
        ->and($json['properties']['present']['type'])->toBe(['string', 'null'])
        ->and($json['properties']['required']['type'])->toBe('integer')
        ->and($json['properties']['conditional'])->toEqual(['type' => 'string', 'minLength' => 1]);
});

test('Laravel parser normalizes string rules, aliases and parameters independently of rule order', function () {
    $json = generatedValidationSchema([
        'id' => 'between:-2,5|int|required',
        'enabled' => ['bool', 'sometimes'],
        'price' => ['max:10.5', 'numeric', 'min:0.25'],
        'code' => ['between:2,8', 'string', new TitleAttribute('Código')],
    ]);

    expect($json['properties']['id'])->toEqual(['type' => 'integer', 'minimum' => -2, 'maximum' => 5])
        ->and($json['properties']['enabled'])->toEqual(['type' => 'boolean'])
        ->and($json['properties']['price'])->toEqual(['type' => 'number', 'minimum' => 0.25, 'maximum' => 10.5])
        ->and($json['properties']['code'])->toEqual(['type' => 'string', 'minLength' => 2, 'maxLength' => 8, 'description' => 'Código']);
});

test('database rules do not infer an identifier type or minimum and do not query the database', function () {
    $json = generatedValidationSchema([
        'id' => 'string|exists:missing_table,id',
        'email' => 'string|email|unique:missing_table,email',
    ]);

    expect($json['properties']['id'])->toEqual(['type' => 'string'])
        ->and($json['properties']['email'])->toEqual(['type' => 'string', 'format' => 'email']);
    expect(fn () => generatedValidationSchema(['id' => 'exists:users,id']))
        ->toThrow(InvalidArgumentException::class, 'unambiguous type');
});

test('numeric bounds are intersected and rounded safely for integer values and string lengths', function () {
    $json = generatedValidationSchema([
        'count' => ['numeric', 'integer', 'min:2.5', 'min:1', 'max:10', 'max:8.5'],
        'name' => 'string|min:1|min:3|max:20|max:10',
        'largest' => 'integer|max:'.PHP_INT_MAX,
    ]);

    expect($json['properties']['count'])->toEqual(['type' => 'integer', 'minimum' => 3, 'maximum' => 8])
        ->and($json['properties']['name'])->toEqual(['type' => 'string', 'minLength' => 3, 'maxLength' => 10])
        ->and($json['properties']['largest']['maximum'])->toBe(PHP_INT_MAX);
});

test('unsupported rules report the affected rule and field instead of falling back to string', function (array $rules, string $message) {
    expect(fn () => generatedValidationSchema($rules))->toThrow(InvalidArgumentException::class, $message);
})->with([
    [['rating' => 'integer|custom_check'], 'Rule [CustomCheck] on field [rating]'],
    [['token' => ['string', static function () {}]], 'on field [token] requires an explicit schema override'],
    [['name' => ['string', new TitleAttribute('Name')], 'other' => new TitleAttribute('Other')], 'Field [other] needs an unambiguous type'],
    [['items.*.id' => 'integer'], 'Nested validation field [items.*.id]'],
    [['value' => 'string|numeric'], 'Field [value] needs an unambiguous type'],
    [['price' => 'numeric|min:invalid'], 'requires finite numeric parameters'],
    [['rating' => 'integer|between:1'], 'expects 2 numeric parameters'],
    [['flag' => 'boolean|min:1'], 'Size rules on field [flag]'],
    [['value' => 'integer|max:1e30'], 'exceeds the supported integer range'],
]);

test('explicit schema overrides handle custom rules and own the complete field contract', function () {
    $factory = new JsonSchemaTypeFactory;
    $json = generatedValidationSchema([
        'token' => ['required', static function () {}],
        'name' => 'string',
    ], ['token' => $factory->string()->required()->min(10)->description('Token externo')]);

    expect($json['required'])->toBe(['token'])
        ->and($json['properties']['token'])->toEqual([
            'type' => 'string', 'minLength' => 10, 'description' => 'Token externo',
        ])
        ->and($json['properties']['name'])->toEqual(['type' => 'string']);

    expect(fn () => generatedValidationSchema(['name' => 'string'], ['typo' => $factory->string()]))
        ->toThrow(InvalidArgumentException::class, 'must reference fields declared');
});
