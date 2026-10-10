<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;

class ReviewListValidation
{
    public function getRules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', 'exists:users,id', new TitleAttribute('ID do usuário')],
            'rating' => ['sometimes', 'integer', 'between:1,5', new TitleAttribute('Nota da avaliação')],
        ];
    }
}
