<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;

class OrderStatusValidation
{
    public function getRules(): array
    {
        return [
            'status_id' => ['required', 'integer', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
        ];
    }
}
