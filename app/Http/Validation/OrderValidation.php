<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;

class OrderValidation
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function getRules(): array
    {
        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'after' => ['sometimes', 'date', new TitleAttribute('Pedidos depois dessa data')],
            'before' => ['sometimes', 'date', new TitleAttribute('Pedidos antes dessa data')],
        ];
    }
}
