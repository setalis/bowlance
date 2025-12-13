<?php

namespace App\Http\Controllers;

use App\Helpers\MenuHelper;
use Illuminate\View\View;

class IconPreviewController extends Controller
{
    public function index(): View
    {
        return view('pages.icons.preview', [
            'icons' => MenuHelper::getAllAvailableIcons(),
        ]);
    }
}
