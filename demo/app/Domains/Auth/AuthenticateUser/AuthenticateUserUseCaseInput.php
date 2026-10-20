<?php

declare(strict_types=1);

namespace App\Domains\Auth\AuthenticateUser;

final readonly class AuthenticateUserUseCaseInput
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}
}
