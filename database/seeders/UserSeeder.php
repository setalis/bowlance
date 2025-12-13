<?php

namespace Database\Seeders;
 
use App\Enums\RoleName;
use App\Models\City;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
 
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createAdminUser();
        $this->createVendorUser();
    }
 
    public function createAdminUser()
    {
        User::create([
            'name'     => 'Admin User',
            'email'    => 'slavrtm@gmail.com',
            'password' => bcrypt('77788399'),
        ])->roles()->sync(Role::where('name', RoleName::ADMIN->value)->first());
    }

    public function createVendorUser() 
    { 
        $vendor = User::create([ 
            'name'     => 'Restaurant owner', 
            'email'    => 'vendor@admin.com', 
            'password' => bcrypt('password'), 
        ]); 
 
        $vendor->roles()->sync(Role::where('name', RoleName::VENDOR->value)->first()); 

        $vendor->restaurants()->create([ 
            'owner_id' => $vendor->id,
            'city_id' => City::where('name', 'Батуми')->first()->id, 
            'name'    => 'Bowlance', 
            'address' => 'ул. Лермонтова 14', 
        ]);
    } 
}