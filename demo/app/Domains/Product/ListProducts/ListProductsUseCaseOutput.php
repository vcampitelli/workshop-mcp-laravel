<?php

declare(strict_types=1);

namespace App\Domains\Product\ListProducts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListProductsUseCaseOutput
{
    /** @param Collection<int, Product> $products */
    public function __construct(
        public Collection $products,
    ) {}
}
