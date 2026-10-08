<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Admin',
            'description' => 'System Administrator'
        ]);

        Role::firstOrCreate(['slug' => 'receptionist'], [
            'name' => 'Receptionist',
            'description' => 'Front Office Staff'
        ]);

        Role::firstOrCreate(['slug' => 'warehouse'], [
            'name' => 'Warehouse',
            'description' => 'Inventory Staff'
        ]);
    }
}
