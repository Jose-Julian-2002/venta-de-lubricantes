<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function login(array $credentials): void
    {
        if (! Auth::guard('web')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'estado' => true,
        ])) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed', [], 'es'),
            ]);
        }
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
