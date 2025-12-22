<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConstructorProductRequest;
use App\Http\Requests\Admin\UpdateConstructorProductRequest;
use App\Models\ConstructorCategory;
use App\Models\ConstructorProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConstructorProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('pages.admin.constructor-products.index', [
            'products' => ConstructorProduct::with('category')
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
        return view('pages.admin.constructor-products.create', [
            'categories' => ConstructorCategory::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConstructorProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('constructor-products', 'public');
        }

        ConstructorProduct::create($data);

        return to_route('admin.constructor-products.index')
            ->with('status', 'Продукт конструктора успешно создан.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConstructorProduct $constructorProduct): View
    {
        return view('pages.admin.constructor-products.edit', [
            'product' => $constructorProduct,
            'categories' => ConstructorCategory::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConstructorProductRequest $request, ConstructorProduct $constructorProduct): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($constructorProduct->image) {
                Storage::disk('public')->delete($constructorProduct->image);
            }
            $data['image'] = $request->file('image')->store('constructor-products', 'public');
        }

        $constructorProduct->update($data);

        return to_route('admin.constructor-products.index')
            ->with('status', 'Продукт конструктора успешно обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConstructorProduct $constructorProduct): RedirectResponse
    {
        if ($constructorProduct->image) {
            Storage::disk('public')->delete($constructorProduct->image);
        }

        $constructorProduct->delete();

        return to_route('admin.constructor-products.index')
            ->with('status', 'Продукт конструктора успешно удален.');
    }
}
