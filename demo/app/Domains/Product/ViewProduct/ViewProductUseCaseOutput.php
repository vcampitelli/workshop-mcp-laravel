<?php

declare(strict_types=1);

namespace App\Domains\Product\ViewProduct;

use App\Domains\Shared\UseCaseError;
use App\Models\Product;

final readonly class ViewProductUseCaseOutput
{
    public function __construct(
        public ?Product $product = null,
        public ?UseCaseError $error = null,
    ) {}
}
