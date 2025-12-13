<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\City;
use App\Models\Restaurant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::all();
        $vendors = User::whereHas('roles', function ($query) {
            $query->where('name', RoleName::VENDOR->value);
        })->get();

        if ($cities->isEmpty() || $vendors->isEmpty()) {
            $this->command->warn('Необходимо сначала запустить CitySeeder и UserSeeder');
            return;
        }

        $restaurants = [
            [
                'name' => 'Bowlance',
                'address' => 'ул. Лермонтова 14',
                'city' => 'Батуми',
            ],
            [
                'name' => 'Cafe Central',
                'address' => 'пр. Руставели 15',
                'city' => 'Тбилиси',
            ],
            [
                'name' => 'Restaurant Georgia',
                'address' => 'ул. Агмашенебели 20',
                'city' => 'Тбилиси',
            ],
            [
                'name' => 'Sea View',
                'address' => 'ул. Пушкина 5',
                'city' => 'Батуми',
            ],
        ];

        foreach ($restaurants as $index => $restaurantData) {
            $city = $cities->firstWhere('name', $restaurantData['city']);
            $vendor = $vendors->get($index % $vendors->count());

            if ($city) {
                Restaurant::create([
                    'owner_id' => $vendor->id,
                    'city_id' => $city->id,
                    'name' => $restaurantData['name'],
                    'address' => $restaurantData['address'],
                ]);
            }
        }
    }
}
