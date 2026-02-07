<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class BootstrapSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'manager']);
        Role::firstOrCreate(['name' => 'cashier']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.local'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );
        $admin->syncRoles(['admin']);

        $cashier = User::firstOrCreate(
            ['email' => 'kasir@demo.local'],
            ['name' => 'Kasir', 'password' => Hash::make('password')]
        );
        $cashier->syncRoles(['cashier']);
    }
}