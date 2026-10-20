<?php

declare(strict_types=1);

namespace App\Domains\Review\ListProductReviews;

final readonly class ListProductReviewsUseCaseInput
{
    public function __construct(
        public int $productId,
        public ?int $userId = null,
        public ?int $rating = null,
    ) {}
}
