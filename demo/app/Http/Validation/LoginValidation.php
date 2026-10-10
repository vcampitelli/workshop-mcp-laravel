<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;

class LoginValidation
{
    public function getRules(): array
    {
        return [
            'email' => ['required', 'email', new TitleAttribute('E-mail do usuário')],
            'password' => ['required', 'string', new TitleAttribute('Senha do usuário')],
        ];
    }
}
