<?php

declare(strict_types=1);

namespace App\Domains\Auth\AuthenticateUser;

use App\Domains\Shared\UseCaseInput;
use App\Rules\TitleAttribute;
use Illuminate\Contracts\Validation\ValidationRule;

/** @extends UseCaseInput<array{email: string, password: string, remember?: bool|int|string}> */
final readonly class AuthenticateUserUseCaseInput extends UseCaseInput
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}

    /** @return array<string, list<string|ValidationRule>> */
    public static function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', new TitleAttribute('E-mail do usuário')],
            'password' => ['required', 'string', new TitleAttribute('Senha do usuário')],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /** @param array{email: string, password: string, remember?: bool|int|string} $data */
    protected static function fromValidatedArray(array $data): static
    {
        return new self(
            email: (string) $data['email'],
            password: (string) $data['password'],
            remember: isset($data['remember']) ? (bool) $data['remember'] : false,
        );
    }
}
