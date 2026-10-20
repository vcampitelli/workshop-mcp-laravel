<?php

declare(strict_types=1);

namespace App\Domains\Auth\AuthenticateUser;

use App\Domains\Shared\UseCaseError;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class AuthenticateUserUseCase
{
    public function __invoke(AuthenticateUserUseCaseInput $input): AuthenticateUserUseCaseOutput
    {
        $guard = Auth::guard('web');
        if (! $guard->attempt(['email' => $input->email, 'password' => $input->password], $input->remember)) {
            return new AuthenticateUserUseCaseOutput(error: UseCaseError::InvalidInput);
        }
        $user = $guard->user();

        return $user instanceof User
            ? new AuthenticateUserUseCaseOutput($user)
            : new AuthenticateUserUseCaseOutput(error: UseCaseError::InvalidInput);
    }
}
