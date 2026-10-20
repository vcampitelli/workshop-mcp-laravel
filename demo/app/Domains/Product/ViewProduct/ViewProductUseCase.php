<?php

declare(strict_types=1);

namespace App\Domains\Product\ViewProduct;

use App\Domains\Shared\UseCaseError;
use App\Models\Product;

final class ViewProductUseCase
{
    public function __invoke(int $id): ViewProductUseCaseOutput
    {
        $product = Product::query()->with(['category', 'productVariants.productAttribute'])->find($id);

        return $product === null
            ? new ViewProductUseCaseOutput(error: UseCaseError::NotFound)
            : new ViewProductUseCaseOutput(product: $product);
    }
}
