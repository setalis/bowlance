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
            $botUrl = "https://t.me/{$botUsername}?start={$verification->verification_token}";

            return response()->json([
                'success' => true,
                'bot_url' => $botUrl,
                'verification_token' => $verification->verification_token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании верификации. Попробуйте позже.',
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
}
