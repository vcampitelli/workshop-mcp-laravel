<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderStatusValidation
{
    /** @return array<string, list<string|ValidationRule>> */
    public function getRules(): array
    {
        return [
            'status_id' => ['required', 'integer', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
        ];
    }
}
