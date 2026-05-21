<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim((string) $this->input('identifier'));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            if (! Auth::attempt(['email' => $login, 'password' => $password], $remember)) {
                $this->failedAttempt();
            }
        } else {
            $this->authenticateByIdentifier($login, $password, $remember);
        }

        $user = Auth::user();

        if ($user->status === 'rejected') {
            Auth::logout();

            throw ValidationException::withMessages([
                'identifier' => 'Votre compte a été rejeté par l\'administration.',
            ]);
        }

        if ($user->status !== 'approved') {
            Auth::logout();

            throw ValidationException::withMessages([
                'identifier' => 'Votre compte est en attente de validation par l\'administration.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function authenticateByIdentifier(string $login, string $password, bool $remember): void
    {
        $matches = User::withoutGlobalScopes()->where('identifier', $login)->get();

        if ($matches->isEmpty()) {
            $this->failedAttempt();
        }

        $authenticated = $matches->filter(
            fn (User $user) => Hash::check($password, $user->password)
        );

        if ($authenticated->count() !== 1) {
            $this->failedAttempt();
        }

        Auth::login($authenticated->first(), $remember);
    }

    private function failedAttempt(): never
    {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'identifier' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower(trim((string) $this->input('identifier'))).'|'.$this->ip());
    }
}
