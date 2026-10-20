<?php

declare(strict_types=1);

namespace App\Domains\Review\ListProductReviews;

use App\Domains\Shared\UseCaseError;
use App\Models\Product;

final class ListProductReviewsUseCase
{
    public function __invoke(ListProductReviewsUseCaseInput $input): ListProductReviewsUseCaseOutput
    {
        $product = Product::query()->find($input->productId);
        if ($product === null) {
            return new ListProductReviewsUseCaseOutput(error: UseCaseError::NotFound);
        }
        $query = $product->reviews();
        if ($input->userId !== null) {
            $query->where('user_id', $input->userId);
        }
        if ($input->rating !== null) {
            $query->where('rating', $input->rating);
        }

        return new ListProductReviewsUseCaseOutput($query->with('user')->get());
    }
}
