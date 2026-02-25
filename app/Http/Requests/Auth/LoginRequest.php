<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 1. Definimos que el campo que viene del formulario se llama 'login'
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * 2. Lógica híbrida para autenticar
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginValue = $this->input('login');

        // Determinamos si el usuario ingresó un correo o un código
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'codigo_empleado';

        // Intentamos el login usando el campo dinámico ($field)
        if (! Auth::attempt([$field => $loginValue, 'password' => $this->password], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('auth.failed'), // El error se devuelve al campo 'login' en Vue
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * 3. Control de intentos fallidos (Rate Limiting)
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * 4. La llave del limitador también debe usar 'login'
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}