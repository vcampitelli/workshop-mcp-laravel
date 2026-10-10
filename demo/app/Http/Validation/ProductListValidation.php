<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;

class ProductListValidation
{
    public function getRules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', new TitleAttribute('Nome do produto')],
            'product_category_id' => ['sometimes', 'integer', 'exists:product_categories,id', new TitleAttribute('ID da categoria')],
            'product_status_id' => ['sometimes', 'integer', 'exists:product_statuses,id', new TitleAttribute('ID do status do produto')],
            'price_lt' => ['sometimes', 'numeric', 'min:0', new TitleAttribute('Preço menor que')],
            'price_gt' => ['sometimes', 'numeric', 'min:0', new TitleAttribute('Preço maior que')],
        ];
    }
}
