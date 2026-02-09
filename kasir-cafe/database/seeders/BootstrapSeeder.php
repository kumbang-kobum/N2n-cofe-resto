<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Unit;
use App\Models\UnitConversion;

class BootstrapSeeder extends Seeder
{
    public function run(): void
    {
        // === Units ===
        $g   = Unit::firstOrCreate(['symbol' => 'g'],   ['name' => 'Gram']);
        $kg  = Unit::firstOrCreate(['symbol' => 'kg'],  ['name' => 'Kilogram']);
        $ml  = Unit::firstOrCreate(['symbol' => 'ml'],  ['name' => 'Milliliter']);
        $l   = Unit::firstOrCreate(['symbol' => 'l'],   ['name' => 'Liter']);
        $pcs = Unit::firstOrCreate(['symbol' => 'pcs'], ['name' => 'Pieces']);

        // === Unit Conversions ===
        UnitConversion::firstOrCreate(
            ['from_unit_id' => $kg->id, 'to_unit_id' => $g->id],
            ['multiplier' => 1000]
        );

        UnitConversion::firstOrCreate(
            ['from_unit_id' => $g->id, 'to_unit_id' => $kg->id],
            ['multiplier' => 0.001]
        );

        UnitConversion::firstOrCreate(
            ['from_unit_id' => $l->id, 'to_unit_id' => $ml->id],
            ['multiplier' => 1000]
        );

        UnitConversion::firstOrCreate(
            ['from_unit_id' => $ml->id, 'to_unit_id' => $l->id],
            ['multiplier' => 0.001]
        );

        // === Roles ===
        $adminRole   = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);

        // === Users ===
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.local'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );
        $admin->syncRoles([$adminRole]);

        $cashier = User::firstOrCreate(
            ['email' => 'kasir@demo.local'],
            ['name' => 'Kasir', 'password' => Hash::make('password')]
        );
        $cashier->syncRoles([$cashierRole]);

        $manager = User::firstOrCreate(
            ['email' => 'manager@demo.local'],
            ['name' => 'Manager', 'password' => Hash::make('password')]
        );
        $manager->syncRoles([$managerRole]);
    }
}
