<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\ViewProductCategory;

use App\Domains\Shared\UseCaseError;
use App\Models\ProductCategory;

final class ViewProductCategoryUseCase
{
    public function __invoke(int $id): ViewProductCategoryUseCaseOutput
    {
        $category = ProductCategory::query()->with(['products'])->find($id);

        return $category === null
            ? new ViewProductCategoryUseCaseOutput(error: UseCaseError::NotFound)
            : new ViewProductCategoryUseCaseOutput(category: $category);
    }
}
