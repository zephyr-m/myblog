<?php

namespace App\n2_System\Http\Controllers;

use App\n1_Infra\Http\Controllers\Controller;
use App\n2_System\Access\Models\Permission;
use App\n2_System\Access\Models\Role;
use App\n2_System\Identity\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $user->loadMissing('roles.permissions');
        $canViewUsers = $user->canDo('user.view');
        $canViewRoles = $user->canDo('role.view');
        $stats = [];

        if ($canViewUsers) {
            $stats[] = [
                'code' => 'users',
                'label' => 'Пользователи',
                'value' => User::query()->count(),
                'description' => 'Всего учётных записей',
            ];
        }

        if ($canViewRoles) {
            $stats[] = [
                'code' => 'roles',
                'label' => 'Роли',
                'value' => Role::query()->count(),
                'description' => 'Ролей в системе',
            ];
            $stats[] = [
                'code' => 'permissions',
                'label' => 'Права',
                'value' => Permission::query()->count(),
                'description' => 'Системных разрешений',
            ];
        }

        if ($canViewUsers) {
            $stats[] = [
                'code' => 'users_without_roles',
                'label' => 'Без роли',
                'value' => User::query()->doesntHave('roles')->count(),
                'description' => 'Требуют настройки доступа',
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentUsers' => $canViewUsers
                ? User::query()
                    ->with('roles:id,name,code')
                    ->latest()
                    ->limit(5)
                    ->get(['id', 'name', 'email', 'created_at'])
                : [],
            'accessRoles' => $user->roles
                ->map(fn (Role $role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'code' => $role->code,
                ])
                ->values(),
        ]);
    }
}
