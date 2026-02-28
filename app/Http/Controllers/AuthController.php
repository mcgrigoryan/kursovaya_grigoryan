<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private ActivityLogger $logger
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Логин обязателен',
            'password.required' => 'Пароль обязателен',
        ]);

        if (Auth::attempt([
            'login' => $credentials['login'],
            'password' => $credentials['password'],
            'status' => 'active',
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $this->logger->log('Успешный вход в систему');

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'login' => 'Неверный логин или пароль',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->logger->log('Выход из системы');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
