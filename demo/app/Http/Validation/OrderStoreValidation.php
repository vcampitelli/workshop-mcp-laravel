<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;

class OrderStoreValidation
{
    public function getRules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id,deleted_at,NULL', new TitleAttribute('ID do usuário')],
            'status_id' => ['required', 'integer', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
            'total_amount' => ['required', 'numeric', 'min:0', new TitleAttribute('Valor total do pedido')],
        ];
    }
}
