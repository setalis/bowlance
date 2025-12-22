<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConstructorCategoryRequest;
use App\Http\Requests\Admin\UpdateConstructorCategoryRequest;
use App\Models\ConstructorCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConstructorCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('pages.admin.constructor-categories.index', [
            'categories' => ConstructorCategory::withCount('products')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.admin.constructor-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConstructorCategoryRequest $request): RedirectResponse
    {
        ConstructorCategory::create($request->validated());

        return to_route('admin.constructor-categories.index')
            ->with('status', 'Категория конструктора успешно создана.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConstructorCategory $constructorCategory): View
    {
        return view('pages.admin.constructor-categories.edit', [
            'category' => $constructorCategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConstructorCategoryRequest $request, ConstructorCategory $constructorCategory): RedirectResponse
    {
        $constructorCategory->update($request->validated());

        return to_route('admin.constructor-categories.index')
            ->with('status', 'Категория конструктора успешно обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConstructorCategory $constructorCategory): RedirectResponse
    {
        $constructorCategory->delete();

        return to_route('admin.constructor-categories.index')
            ->with('status', 'Категория конструктора успешно удалена.');
    }
}
