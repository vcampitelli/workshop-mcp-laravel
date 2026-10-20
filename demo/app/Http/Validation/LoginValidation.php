<?php

namespace App\Http\Validation;

use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

class LoginValidation
{
    /** @return array<string, list<string|ValidationRule>> */
    public function getRules(): array
    {
        return [
            'email' => ['required', 'email', new TitleAttribute('E-mail do usuário')],
            'password' => ['required', 'string', new TitleAttribute('Senha do usuário')],
        ];
    }
}
