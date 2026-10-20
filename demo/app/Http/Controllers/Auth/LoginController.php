<?php

namespace App\Http\Controllers\Auth;

use App\Domains\Auth\AuthenticateUser\AuthenticateUserUseCase;
use App\Domains\Auth\AuthenticateUser\AuthenticateUserUseCaseInput;
use App\Domains\Auth\Logout\LogoutUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AuthenticateUserUseCase $useCase): RedirectResponse
    {
        $output = $useCase(AuthenticateUserUseCaseInput::fromArray($request->all()));

        if ($output->error !== null) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha inválidos.']);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function destroy(Request $request, LogoutUseCase $useCase): RedirectResponse
    {
        $useCase();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
