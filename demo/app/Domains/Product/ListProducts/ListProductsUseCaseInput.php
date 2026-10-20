<?php

declare(strict_types=1);

namespace App\Domains\Product\ListProducts;

final readonly class ListProductsUseCaseInput
{
    public function __construct(
        public ?string $name = null,
        public ?int $categoryId = null,
        public ?int $statusId = null,
        public ?float $priceLt = null,
        public ?float $priceGt = null,
    ) {}
}
