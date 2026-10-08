<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $receptionistRole = Role::where('slug', 'receptionist')->first();
        $warehouseRole = Role::where('slug', 'warehouse')->first();

        User::firstOrCreate(['email' => 'admin@hotel.test'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'receptionist@hotel.test'], [
            'name' => 'Front Desk',
            'password' => Hash::make('password'),
            'role_id' => $receptionistRole->id,
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'warehouse@hotel.test'], [
            'name' => 'Inventory Manager',
            'password' => Hash::make('password'),
            'role_id' => $warehouseRole->id,
            'is_active' => true,
        ]);
    }
}
