<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'System Administrator'
        ]);

        Role::create([
            'name' => 'Receptionist',
            'slug' => 'receptionist',
            'description' => 'Front Office Staff'
        ]);

        Role::create([
            'name' => 'Warehouse',
            'slug' => 'warehouse',
            'description' => 'Inventory Staff'
        ]);
    }
}
