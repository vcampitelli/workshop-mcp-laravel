<?php

declare(strict_types=1);

namespace App\Domains\Auth\AuthenticateUser;

use App\Domains\Shared\UseCaseError;
use App\Models\User;

final readonly class AuthenticateUserUseCaseOutput
{
    public function __construct(
        public ?User $user = null,
        public ?UseCaseError $error = null,
    ) {}
}
