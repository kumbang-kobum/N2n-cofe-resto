<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $roleFilter = $request->query('role', '');

        $users = User::query()
            ->with(['roles', 'permissions'])
            ->when($roleFilter !== '', function ($q) use ($roleFilter) {
                $q->whereHas('roles', fn ($r) => $r->where('name', $roleFilter));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = ['admin', 'manager', 'cashier', 'petugas'];

        return view('admin.users.index', compact('users', 'roles', 'roleFilter'));
    }

    public function create()
    {
        $roles = ['admin', 'manager', 'cashier', 'petugas'];
        $permissionGroups = $this->permissionGroups();
        $roleDefaultPermissions = config('menu_permissions.defaults', []);

        return view('admin.users.create', compact('roles', 'permissionGroups', 'roleDefaultPermissions'));
    }

    public function store(Request $request)
    {
        $roles = ['admin', 'manager', 'cashier', 'petugas'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in($roles)],
            'permissions_present' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($this->availablePermissions())],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles([$data['role']]);
        $user->syncPermissions($this->resolvePermissions($request, $data['role']));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = ['admin', 'manager', 'cashier', 'petugas'];
        $currentRole = $user->getRoleNames()->first();
        $permissionGroups = $this->permissionGroups();
        $roleDefaultPermissions = config('menu_permissions.defaults', []);
        $selectedPermissions = $user->permissions->pluck('name')->all();

        return view('admin.users.edit', compact(
            'user',
            'roles',
            'currentRole',
            'permissionGroups',
            'roleDefaultPermissions',
            'selectedPermissions',
        ));
    }

    public function update(Request $request, User $user)
    {
        $roles = ['admin', 'manager', 'cashier', 'petugas'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in($roles)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'permissions_present' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($this->availablePermissions())],
        ]);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        $user->syncRoles([$data['role']]);
        $user->syncPermissions($this->resolvePermissions($request, $data['role']));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Tidak bisa menghapus akun yang sedang login.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna berhasil dihapus.');
    }

    protected function availablePermissions(): array
    {
        return array_keys(config('menu_permissions.permissions', []));
    }

    protected function permissionGroups(): array
    {
        $definitions = config('menu_permissions.permissions', []);

        return collect(config('menu_permissions.groups', []))
            ->map(function (array $permissionNames) use ($definitions) {
                return collect($permissionNames)
                    ->map(fn (string $permissionName) => array_merge(
                        ['name' => $permissionName],
                        $definitions[$permissionName] ?? ['label' => $permissionName, 'description' => ''],
                    ))
                    ->all();
            })
            ->all();
    }

    protected function resolvePermissions(Request $request, string $role): array
    {
        if ($request->boolean('permissions_present')) {
            return array_values(array_unique($request->input('permissions', [])));
        }

        return config("menu_permissions.defaults.{$role}", []);
    }
}
