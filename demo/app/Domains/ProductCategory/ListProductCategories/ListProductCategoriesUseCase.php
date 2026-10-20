<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\ListProductCategories;

use App\Models\ProductCategory;

final class ListProductCategoriesUseCase
{
    public function __invoke(ListProductCategoriesUseCaseInput $input): ListProductCategoriesUseCaseOutput
    {
        $query = ProductCategory::query();
        if ($input->name !== null) {
            $query->where('name', 'like', '%'.$input->name.'%');
        }
        if ($input->parentId !== null) {
            $query->where('parent_id', $input->parentId);
        }

        return new ListProductCategoriesUseCaseOutput($query->get());
    }
}
