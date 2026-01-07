<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TelegramReturnController extends Controller
{
    public function show(Request $request): View
    {
        $orderId = $request->query('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::find($orderId);
        }

        // Формируем URL для редиректа
        $redirectUrl = $order
            ? route('home').'?order_id='.$orderId
            : route('home');

        return view('pages.frontend.telegram-return', [
            'order' => $order,
            'redirectUrl' => $redirectUrl,
        ]);
    }
}
