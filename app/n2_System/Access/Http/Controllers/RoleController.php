<?php

namespace App\n2_System\Access\Http\Controllers;

use App\n1_Infra\Http\Controllers\Controller;
use App\n2_System\Access\Http\Requests\UpdateRolePermissionsRequest;
use App\n2_System\Access\Models\Permission;
use App\n2_System\Access\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function edit(): Response
    {
        Gate::authorize('permission', 'role.view');

        return Inertia::render('system/Roles', [
            'roles' => Role::query()
                ->withCount('users')
                ->with('permissions:id,code')
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'code' => $role->code,
                    'users_count' => $role->users_count,
                    'permission_ids' => $role->permissions->pluck('id'),
                    'locked' => $role->code === Role::SUPER_ADMIN,
                ]),
            'permissions' => Permission::query()->orderBy('code')->get(['id', 'name', 'code']),
        ]);
    }

    public function update(UpdateRolePermissionsRequest $request): RedirectResponse
    {
        Gate::authorize('permission', 'role.update');
        $matrix = $request->matrix();

        DB::transaction(function () use ($matrix): void {
            $roles = Role::query()
                ->where('code', '!=', Role::SUPER_ADMIN)
                ->lockForUpdate()
                ->get();
            $submitted = array_keys($matrix);
            $editable = $roles->modelKeys();
            sort($submitted);
            sort($editable);

            if ($submitted !== $editable) {
                throw ValidationException::withMessages([
                    'roles' => 'Матрица должна содержать все управляемые роли.',
                ]);
            }

            $roleUpdate = Permission::query()->where('code', 'role.update')->firstOrFail();

            foreach ($roles as $role) {
                $permissions = array_values(array_unique($matrix[$role->id] ?? []));

                if (in_array($roleUpdate->id, $permissions, true)) {
                    throw ValidationException::withMessages([
                        'roles' => 'Изменение ролей доступно только суперадминистратору.',
                    ]);
                }

                $role->permissions()->sync($permissions);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Права ролей обновлены.']);

        return back();
    }
}
