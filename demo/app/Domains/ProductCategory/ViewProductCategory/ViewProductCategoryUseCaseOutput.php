<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\ViewProductCategory;

use App\Domains\Shared\UseCaseError;
use App\Models\ProductCategory;

final readonly class ViewProductCategoryUseCaseOutput
{
    public function __construct(
        public ?ProductCategory $category = null,
        public ?UseCaseError $error = null,
    ) {}
}
