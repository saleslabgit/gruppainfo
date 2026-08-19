<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Domain\User\UserStatus;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $authenticated = Auth::guard('web')->attempt([
            'email' => (string) $this->string('email'),
            'password' => (string) $this->string('password'),
            'status' => UserStatus::Approved->value,
            'disabled' => false,
        ]);

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey(), (int) config('auth.login.decay_seconds'));

            throw ValidationException::withMessages([
                'email' => 'Не удалось войти с указанными данными.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), (int) config('auth.login.max_attempts'))) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Слишком много попыток. Повторите вход через {$seconds} сек.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->string('email')).'|'.$this->ip());
    }
}
