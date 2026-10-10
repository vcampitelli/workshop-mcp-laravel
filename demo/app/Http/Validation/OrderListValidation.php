<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;

class OrderListValidation
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function getRules(): array
    {
        return [
            'status_id' => ['sometimes', 'exists:order_statuses,id', new TitleAttribute('ID do status do pedido')],
            'user_id' => ['sometimes', 'exists:users,id', new TitleAttribute('ID do usuário')],
            'after' => ['sometimes', 'date', new TitleAttribute('Pedidos depois dessa data')],
            'before' => ['sometimes', 'date', new TitleAttribute('Pedidos antes dessa data')],
            'amount_lt' => ['sometimes', 'numeric', new TitleAttribute('Valor menor que')],
            'amount_gt' => ['sometimes', 'numeric', new TitleAttribute('Valor maior que')],
        ];
    }
}
