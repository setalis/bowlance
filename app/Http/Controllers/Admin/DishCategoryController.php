<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDishCategoryRequest;
use App\Http\Requests\Admin\UpdateDishCategoryRequest;
use App\Models\DishCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DishCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('pages.admin.dish-categories.index', [
            'categories' => DishCategory::withCount('dishes')->latest()->paginate(15),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.admin.dish-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishCategoryRequest $request): RedirectResponse
    {
        DishCategory::create($request->validated());

        return to_route('admin.dish-categories.index')
            ->with('status', 'Категория успешно создана.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DishCategory $dishCategory): View
    {
        return view('pages.admin.dish-categories.edit', [
            'category' => $dishCategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishCategoryRequest $request, DishCategory $dishCategory): RedirectResponse
    {
        $dishCategory->update($request->validated());

        return to_route('admin.dish-categories.index')
            ->with('status', 'Категория успешно обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DishCategory $dishCategory): RedirectResponse
    {
        $dishCategory->delete();

        return to_route('admin.dish-categories.index')
            ->with('status', 'Категория успешно удалена.');
    }
}
