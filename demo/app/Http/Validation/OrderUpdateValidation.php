<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;

class OrderUpdateValidation
{
    public function getRules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id,deleted_at,NULL', new TitleAttribute('ID do usuário')],
            'status_id' => ['sometimes', 'required', 'integer', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
            'total_amount' => ['sometimes', 'required', 'numeric', 'min:0', new TitleAttribute('Valor total do pedido')],
        ];
    }
}
