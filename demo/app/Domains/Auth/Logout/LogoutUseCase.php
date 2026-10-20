<?php

declare(strict_types=1);

namespace App\Domains\Auth\Logout;

use Illuminate\Support\Facades\Auth;

final class LogoutUseCase
{
    public function __invoke(): void
    {
        Auth::guard('web')->logout();
    }
}
