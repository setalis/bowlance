<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function index(): View
    {
        $this->authorize('restaurant.viewAny');

        return view('pages.admin.restaurants.index', [
            'restaurants' => Restaurant::with(['city', 'owner'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('restaurant.create');

        return view('pages.admin.restaurants.create', [
            'cities' => City::all(),
            'owners' => User::whereHas('roles', function ($query) {
                $query->where('name', 'vendor');
            })->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('restaurant.create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'exists:cities,id'],
            'owner_id' => ['required', 'exists:users,id'],
        ]);

        Restaurant::create($validated);

        return to_route('admin.restaurants.index')
            ->with('status', 'Ресторан успешно создан.');
    }

    // public function show(Restaurant $restaurant): View
    // {
    //     $this->authorize('restaurant.view');

    //     $restaurant->load(['city', 'owner']);

    //     return view('pages.admin.restaurants.show', [
    //         'restaurant' => $restaurant,
    //     ]);
    // }

    public function edit(Restaurant $restaurant): View
    {
        $this->authorize('restaurant.update');

        return view('pages.admin.restaurants.edit', [
            'restaurant' => $restaurant,
            'cities' => City::all(),
            'owners' => User::whereHas('roles', function ($query) {
                $query->where('name', 'vendor');
            })->get(),
        ]);
    }

    public function update(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $this->authorize('restaurant.update');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'exists:cities,id'],
            'owner_id' => ['required', 'exists:users,id'],
        ]);

        $restaurant->update($validated);

        return to_route('admin.restaurants.index')
            ->with('status', 'Ресторан успешно обновлен.');
    }

    public function destroy(Restaurant $restaurant): RedirectResponse
    {
        $this->authorize('restaurant.delete');

        $restaurant->delete();

        return to_route('admin.restaurants.index')
            ->with('status', __('Restaurant deleted successfully.'));
    }
}