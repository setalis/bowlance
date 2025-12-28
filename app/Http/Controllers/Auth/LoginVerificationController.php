<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginVerification;
use App\Models\User;
use App\Services\TelegramVerificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginVerificationController extends Controller
{
    public function __construct(
        private readonly TelegramVerificationService $telegramService
    ) {}

    public function sendCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->input('phone'))->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с таким телефоном не найден.',
            ], 404);
        }

        // Генерируем токен для входа (код будет создан когда пользователь начнет диалог с ботом)
        $loginToken = LoginVerification::generateToken();
        $expiresAt = Carbon::now()->addMinutes(10);

        // Создаем верификацию с токеном (код будет создан позже)
        LoginVerification::updateOrCreate(
            [
                'user_id' => $user->id,
                'phone' => $user->phone,
            ],
            [
                'code' => null, // Код будет создан когда пользователь начнет диалог
                'login_token' => $loginToken,
                'expires_at' => $expiresAt,
                'attempts' => 0,
                'verified_at' => null,
                'telegram_chat_id' => null, // Будет установлен когда пользователь начнет диалог
            ]
        );

        $botUsername = config('verification.telegram.bot_username');
        if (! $botUsername) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram бот не настроен.',
            ], 500);
        }

        // Формируем ссылку на бота с токеном
        $encodedToken = urlencode($loginToken);
        $botUrl = "https://t.me/{$botUsername}?start=login_{$encodedToken}";

        return response()->json([
            'success' => true,
            'message' => 'Откройте Telegram бота и отправьте команду /start',
            'bot_url' => $botUrl,
            'login_token' => $loginToken,
        ]);
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('phone', $request->input('phone'))->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с таким телефоном не найден.',
            ], 404);
        }

        $verification = LoginVerification::where('user_id', $user->id)
            ->where('phone', $user->phone)
            ->whereNull('verified_at')
            ->whereNotNull('code') // Код должен быть создан
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => 'Код не найден или еще не создан. Откройте Telegram бота и начните диалог.',
            ], 404);
        }

        if ($verification->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Код истек. Запросите новый код.',
            ], 400);
        }

        if ($verification->hasExceededAttempts()) {
            return response()->json([
                'success' => false,
                'message' => 'Превышено количество попыток. Запросите новый код.',
            ], 400);
        }

        if (! $verification->verifyCode($request->input('code'))) {
            $verification->incrementAttempts();

            return response()->json([
                'success' => false,
                'message' => 'Неверный код. Попробуйте еще раз.',
            ], 400);
        }

        // Код правильный - помечаем как проверенный
        $verification->markAsVerified();

        // Используем существующий токен пользователя (или создаем если его нет)
        if (! $user->login_token) {
            // Генерируем токен только если его нет (для старых пользователей)
            $user->update(['login_token' => bin2hex(random_bytes(32))]);
            $user->refresh();
        }
        $loginToken = $user->login_token;

        // Авторизуем пользователя
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Код подтвержден. Вы авторизованы.',
            'login_token' => $loginToken,
            'redirect_url' => route('account.index'),
        ]);
    }
}
