<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhoneVerification\StartVerificationRequest;
use App\Http\Requests\PhoneVerification\StorePhoneVerificationRequest;
use App\Http\Requests\PhoneVerification\VerifyPhoneCodeRequest;
use App\Models\Order;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;

class PhoneVerificationController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationService $verificationService
    ) {}

    public function start(StartVerificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $order = Order::findOrFail($data['order_id']);

        if ($order->status !== 'pending_verification') {
            return response()->json([
                'success' => false,
                'message' => 'Заказ не требует верификации.',
            ], 400);
        }

        try {
            $verification = $this->verificationService->startVerification(
                $order,
                $order->customer_phone
            );

            $botUsername = config('verification.telegram.bot_username');

            if (empty($botUsername)) {
                \Log::error('Telegram bot username not configured');

                return response()->json([
                    'success' => false,
                    'message' => 'Telegram бот не настроен. Обратитесь к администратору.',
                ], 500);
            }

            $botUrl = "https://t.me/{$botUsername}?start={$verification->verification_token}";

            return response()->json([
                'success' => true,
                'bot_url' => $botUrl,
                'verification_token' => $verification->verification_token,
            ]);
        } catch (\TypeError $e) {
            \Log::error('Type error при создании верификации', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? 'Ошибка типа: '.$e->getMessage().' в файле '.$e->getFile().' на строке '.$e->getLine()
                    : 'Ошибка при создании верификации. Проверьте настройки.',
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании верификации', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? 'Ошибка: '.$e->getMessage().' в файле '.$e->getFile().' на строке '.$e->getLine()
                    : 'Ошибка при создании верификации. Попробуйте позже.',
            ], 500);
        }
    }

    public function sendCode(StorePhoneVerificationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $order = Order::findOrFail($data['order_id']);

        if ($order->status !== 'pending_verification') {
            return response()->json([
                'success' => false,
                'message' => 'Заказ не требует верификации.',
            ], 400);
        }

        try {
            $verification = $this->verificationService->initiateVerification(
                $order,
                $data['phone'],
                $data['telegram_chat_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Код подтверждения отправлен в Telegram.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке кода. Попробуйте позже.',
            ], 500);
        }
    }

    public function verifyCode(VerifyPhoneCodeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $order = Order::findOrFail($data['order_id']);

        if ($order->status !== 'pending_verification') {
            return response()->json([
                'success' => false,
                'message' => 'Заказ не требует верификации.',
            ], 400);
        }

        $verified = $this->verificationService->verifyCode($order, $data['code']);

        if (! $verified) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный код или код истек. Попробуйте еще раз.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Телефон успешно подтвержден. Заказ принят.',
            'order' => $order->fresh()->load('items'),
        ]);
    }

    public function checkStatus(): JsonResponse
    {
        try {
            $orderId = request()->query('order_id');

            if (! $orderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ID is required.',
                ], 400);
            }

            $order = Order::find($orderId);

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }

            $verification = $order->phoneVerification;

            return response()->json([
                'success' => true,
                'order_status' => $order->status,
                'is_verified' => $verification ? ($verification->verified_at !== null) : false,
                'has_code' => $verification ? ($verification->code !== null) : false,
                'has_telegram_chat_id' => $verification ? ($verification->telegram_chat_id !== null) : false,
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при проверке статуса верификации', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? 'Ошибка: '.$e->getMessage().' в файле '.$e->getFile().' на строке '.$e->getLine()
                    : 'Ошибка при проверке статуса. Попробуйте позже.',
            ], 500);
        }
    }
}
