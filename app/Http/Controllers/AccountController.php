<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.frontend.account.index', [
            'title' => 'Личный кабинет',
            'user' => $user,
            'orders' => $orders,
        ]);
    }
}
