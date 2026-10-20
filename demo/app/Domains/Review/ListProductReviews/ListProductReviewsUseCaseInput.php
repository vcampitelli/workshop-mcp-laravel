<?php

declare(strict_types=1);

namespace App\Domains\Review\ListProductReviews;

use App\Domains\Shared\UseCaseInput;
use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{product_id: int|string, user_id?: int|string, rating?: int|string}> */
final readonly class ListProductReviewsUseCaseInput extends UseCaseInput
{
    public function __construct(
        public int $productId,
        public ?int $userId = null,
        public ?int $rating = null,
    ) {}

    /** @return array<string, list<string|ValidationRule>> */
    public static function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'min:1'],
            'user_id' => ['sometimes', 'integer', 'exists:users,id', new TitleAttribute('ID do usuário')],
            'rating' => ['sometimes', 'integer', 'between:1,5', new TitleAttribute('Nota da avaliação')],
        ];
    }

    /** @param array{product_id: int|string, user_id?: int|string, rating?: int|string} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            productId: (int) $data['product_id'],
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            rating: isset($data['rating']) ? (int) $data['rating'] : null,
        );
    }
}
