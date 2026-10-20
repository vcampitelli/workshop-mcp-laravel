<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\ListProductCategories;

use App\Domains\Shared\UseCaseInput;
use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{name?: string, parent_id?: int|string}> */
final readonly class ListProductCategoriesUseCaseInput extends UseCaseInput
{
    public function __construct(
        public ?string $name = null,
        public ?int $parentId = null,
    ) {}

    /** @return array<string, list<string|ValidationRule>> */
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', new TitleAttribute('Nome da categoria')],
            'parent_id' => ['sometimes', 'integer', 'exists:product_categories,id', new TitleAttribute('ID da categoria pai')],
        ];
    }

    /** @param array{name?: string, parent_id?: int|string} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            name: isset($data['name']) ? (string) $data['name'] : null,
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
        );
    }
}
