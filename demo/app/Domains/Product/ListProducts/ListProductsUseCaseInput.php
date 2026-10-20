<?php

declare(strict_types=1);

namespace App\Domains\Product\ListProducts;

use App\Domains\Shared\UseCaseInput;
use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{name?: string, product_category_id?: int|string, product_status_id?: int|string, price_lt?: int|float|string, price_gt?: int|float|string}> */
final readonly class ListProductsUseCaseInput extends UseCaseInput
{
    public function __construct(
        public ?string $name = null,
        public ?int $categoryId = null,
        public ?int $statusId = null,
        public ?float $priceLt = null,
        public ?float $priceGt = null,
    ) {}

    /** @return array<string, list<string|ValidationRule>> */
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', new TitleAttribute('Nome do produto')],
            'product_category_id' => ['sometimes', 'integer', 'exists:product_categories,id', new TitleAttribute('ID da categoria')],
            'product_status_id' => ['sometimes', 'integer', 'exists:product_statuses,id', new TitleAttribute('ID do status do produto')],
            'price_lt' => ['sometimes', 'numeric', 'min:0', new TitleAttribute('Preço menor que')],
            'price_gt' => ['sometimes', 'numeric', 'min:0', new TitleAttribute('Preço maior que')],
        ];
    }

    /** @param array{name?: string, product_category_id?: int|string, product_status_id?: int|string, price_lt?: int|float|string, price_gt?: int|float|string} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            name: isset($data['name']) ? (string) $data['name'] : null,
            categoryId: isset($data['product_category_id']) ? (int) $data['product_category_id'] : null,
            statusId: isset($data['product_status_id']) ? (int) $data['product_status_id'] : null,
            priceLt: isset($data['price_lt']) ? (float) $data['price_lt'] : null,
            priceGt: isset($data['price_gt']) ? (float) $data['price_gt'] : null,
        );
    }
}
