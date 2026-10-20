<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\ListProductCategories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListProductCategoriesUseCaseOutput
{
    /** @param Collection<int, ProductCategory> $categories */
    public function __construct(
        public Collection $categories,
    ) {}
}
