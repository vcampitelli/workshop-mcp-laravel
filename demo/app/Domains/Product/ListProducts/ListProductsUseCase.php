<?php

declare(strict_types=1);

namespace App\Domains\Product\ListProducts;

use App\Models\Product;

final class ListProductsUseCase
{
    public function __invoke(ListProductsUseCaseInput $input): ListProductsUseCaseOutput
    {
        $query = Product::query();

        if ($input->name !== null) {
            $query->where('name', 'like', '%'.$input->name.'%');
        }

        if ($input->categoryId !== null) {
            $query->where('product_category_id', '=', $input->categoryId);
        }

        if ($input->statusId !== null) {
            $query->where('product_status_id', '=', $input->statusId);
        }

        if ($input->priceLt !== null) {
            $query->where('price', '<=', $input->priceLt);
        }

        if ($input->priceGt !== null) {
            $query->where('price', '>=', $input->priceGt);
        }

        return new ListProductsUseCaseOutput($query->with(['category'])->get());
    }
}
