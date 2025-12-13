<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDishRequest;
use App\Http\Requests\Admin\UpdateDishRequest;
use App\Models\Dish;
use App\Models\DishCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('pages.admin.dishes.index', [
            'dishes' => Dish::with('category')
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->paginate(15),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.admin.dishes.create', [
            'categories' => DishCategory::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('dishes', 'public');
        }

        $dish = Dish::create($data);
        $dish->load('category');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Блюдо успешно создано.',
                'dish' => $dish,
            ]);
        }

        return to_route('admin.dishes.index')
            ->with('status', 'Блюдо успешно создано.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dish $dish): View
    {
        return view('pages.admin.dishes.edit', [
            'dish' => $dish,
            'categories' => DishCategory::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishRequest $request, Dish $dish): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($dish->image) {
                Storage::disk('public')->delete($dish->image);
            }
            $data['image'] = $request->file('image')->store('dishes', 'public');
        }

        $dish->update($data);
        $dish->load('category');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Блюдо успешно обновлено.',
                'dish' => $dish,
            ]);
        }

        return to_route('admin.dishes.index')
            ->with('status', 'Блюдо успешно обновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dish $dish): RedirectResponse
    {
        if ($dish->image) {
            Storage::disk('public')->delete($dish->image);
        }

        $dish->delete();

        return to_route('admin.dishes.index')
            ->with('status', 'Блюдо успешно удалено.');
    }
}
