<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.signin');
    }

    public function store(Request $request): RedirectResponse
    {
        // Проверяем, вход по телефону или по email
        if ($request->has('login_type') && $request->input('login_type') === 'phone') {
            $request->validate([
                'phone' => ['required', 'string'],
                'login_token' => ['nullable', 'string'],
            ]);

            $this->ensureIsNotRateLimited($request);

            // Ищем пользователя по телефону
            $user = \App\Models\User::where('phone', $request->input('phone'))->first();

            if (! $user) {
                RateLimiter::hit($this->throttleKey($request));

                throw ValidationException::withMessages([
                    'phone' => 'Пользователь с таким телефоном не найден.',
                ]);
            }

            // Проверяем токен из localStorage
            $providedToken = $request->input('login_token');
            if ($providedToken && $user->login_token && hash_equals($user->login_token, $providedToken)) {
                // Токен совпадает - авторизуем пользователя
                Auth::login($user, $request->boolean('remember'));
                RateLimiter::clear($this->throttleKey($request));
                $request->session()->regenerate();

                return redirect()->intended(route('account.index', absolute: false));
            }

            // Если токен не предоставлен или не совпадает
            // Если у пользователя есть токен в базе - нужно запросить код для сохранения токена на новом устройстве
            // Если токена нет в базе - также нужно запросить код (токен будет создан после верификации)
            RateLimiter::hit($this->throttleKey($request));

            if ($user->login_token) {
                throw ValidationException::withMessages([
                    'phone' => 'Токен не найден на этом устройстве. Запросите код через Telegram для сохранения токена.',
                ]);
            } else {
                throw ValidationException::withMessages([
                    'phone' => 'Токен не найден. Запросите код через Telegram.',
                ]);
            }
        }

        // Стандартный вход по email и паролю
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended(route('account.index', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(Request $request): string
    {
        $identifier = $request->input('phone') ?? $request->input('email') ?? 'unknown';

        return Str::transliterate(Str::lower($identifier).'|'.$request->ip());
    }
}
