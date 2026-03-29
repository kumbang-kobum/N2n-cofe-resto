<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'];
        $modelHasPermissionsTable = $tableNames['model_has_permissions'];
        $modelHasRolesTable = $tableNames['model_has_roles'];
        $rolesTable = $tableNames['roles'];
        $guardName = config('auth.defaults.guard', 'web');
        $now = now();

        $actionPermissions = array_filter(
            array_keys(config('menu_permissions.permissions', [])),
            fn (string $name) => str_starts_with($name, 'action.'),
        );

        foreach ($actionPermissions as $permissionName) {
            DB::table($permissionsTable)->updateOrInsert(
                ['name' => $permissionName, 'guard_name' => $guardName],
                ['created_at' => $now, 'updated_at' => $now],
            );
        }

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', $actionPermissions)
            ->pluck('id', 'name');

        $roles = DB::table($rolesTable)->pluck('id', 'name');
        $defaults = config('menu_permissions.defaults', []);

        foreach ($defaults as $roleName => $permissions) {
            $roleId = $roles[$roleName] ?? null;
            if (! $roleId) {
                continue;
            }

            $actionDefaults = array_filter($permissions, fn (string $name) => str_starts_with($name, 'action.'));
            if ($actionDefaults === []) {
                continue;
            }

            $userIds = DB::table($modelHasRolesTable)
                ->where('role_id', $roleId)
                ->where('model_type', User::class)
                ->pluck('model_id');

            foreach ($userIds as $userId) {
                foreach ($actionDefaults as $permissionName) {
                    $permissionId = $permissionIds[$permissionName] ?? null;
                    if (! $permissionId) {
                        continue;
                    }

                    DB::table($modelHasPermissionsTable)->updateOrInsert(
                        [
                            'permission_id' => $permissionId,
                            'model_id' => $userId,
                            'model_type' => User::class,
                        ],
                        []
                    );
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'];
        $modelHasPermissionsTable = $tableNames['model_has_permissions'];

        $actionPermissions = array_filter(
            array_keys(config('menu_permissions.permissions', [])),
            fn (string $name) => str_starts_with($name, 'action.'),
        );

        $permissionIds = DB::table($permissionsTable)
            ->whereIn('name', $actionPermissions)
            ->pluck('id');

        DB::table($modelHasPermissionsTable)
            ->whereIn('permission_id', $permissionIds)
            ->where('model_type', User::class)
            ->delete();

        DB::table($permissionsTable)
            ->whereIn('name', $actionPermissions)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
