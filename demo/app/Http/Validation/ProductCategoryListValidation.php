<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductCategoryListValidation
{
    /** @return array<string, list<string|ValidationRule>> */
    public function getRules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', new TitleAttribute('Nome da categoria')],
            'parent_id' => ['sometimes', 'integer', 'exists:product_categories,id', new TitleAttribute('ID da categoria pai')],
        ];
    }
}
