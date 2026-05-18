<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames             = config('permission.table_names');
        $permissionsTable       = $tableNames['permissions'];
        $modelHasPermissionsTable = $tableNames['model_has_permissions'];
        $rolesTable             = $tableNames['roles'];
        $modelHasRolesTable     = $tableNames['model_has_roles'];
        $guardName              = config('auth.defaults.guard', 'web');
        $now                    = now();

        $permissionName = 'action.refund.approve';

        DB::table($permissionsTable)->updateOrInsert(
            ['name' => $permissionName, 'guard_name' => $guardName],
            ['created_at' => $now, 'updated_at' => $now],
        );

        $permissionId = DB::table($permissionsTable)
            ->where('name', $permissionName)
            ->where('guard_name', $guardName)
            ->value('id');

        // Assign ke semua user dengan role admin dan manager
        foreach (['admin', 'manager'] as $roleName) {
            $role = DB::table($rolesTable)
                ->where('name', $roleName)
                ->where('guard_name', $guardName)
                ->first();

            if (! $role || ! $permissionId) {
                continue;
            }

            $userIds = DB::table($modelHasRolesTable)
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->pluck('model_id');

            foreach ($userIds as $userId) {
                DB::table($modelHasPermissionsTable)->updateOrInsert(
                    ['permission_id' => $permissionId, 'model_id' => $userId, 'model_type' => User::class],
                    []
                );
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $tableNames             = config('permission.table_names');
        $permissionsTable       = $tableNames['permissions'];
        $modelHasPermissionsTable = $tableNames['model_has_permissions'];
        $guardName              = config('auth.defaults.guard', 'web');

        $permissionId = DB::table($permissionsTable)
            ->where('name', 'action.refund.approve')
            ->where('guard_name', $guardName)
            ->value('id');

        if ($permissionId) {
            DB::table($modelHasPermissionsTable)->where('permission_id', $permissionId)->delete();
            DB::table($permissionsTable)->where('id', $permissionId)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
