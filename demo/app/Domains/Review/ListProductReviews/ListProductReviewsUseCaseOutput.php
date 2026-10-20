<?php

declare(strict_types=1);

namespace App\Domains\Review\ListProductReviews;

use App\Domains\Shared\UseCaseError;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListProductReviewsUseCaseOutput
{
    /** @param Collection<int, Review>|null $reviews */
    public function __construct(
        public ?Collection $reviews = null,
        public ?UseCaseError $error = null,
    ) {}
}
