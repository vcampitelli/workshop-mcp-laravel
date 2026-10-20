<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\ListProductCategories;

use App\Models\ProductCategory;

final class ListProductCategoriesUseCase
{
    public function __invoke(?string $name = null, ?int $parentId = null): ListProductCategoriesUseCaseOutput
    {
        $query = ProductCategory::query();
        if ($name !== null) {
            $query->where('name', 'like', '%'.$name.'%');
        }
        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        }

        return new ListProductCategoriesUseCaseOutput($query->get());
    }
}
