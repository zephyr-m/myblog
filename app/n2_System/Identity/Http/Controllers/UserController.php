<?php

namespace App\n2_System\Identity\Http\Controllers;

use App\n1_Infra\Http\Controllers\Controller;
use App\n2_System\Access\Models\Role;
use App\n2_System\Identity\Http\Requests\StoreUserRequest;
use App\n2_System\Identity\Http\Requests\UpdateUserRequest;
use App\n2_System\Identity\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(): Response
    {
        $search = request()->string('search')->trim()->toString();
        $sort = request()->string('sort')->toString();
        $direction = request()->string('direction')->toString();
        $sort = in_array($sort, ['name', 'email', 'created_at'], true) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        return Inertia::render('system/users/Index', [
            'users' => User::query()
                ->with('roles:id,name,code')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                }))
                ->orderBy($sort, $direction)
                ->paginate(15)
                ->withQueryString(),
            'filters' => compact('search', 'sort', 'direction'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('system/users/Form', [
            'roles' => $this->roles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('roles');

        DB::transaction(function () use ($data, $request): void {
            $user = User::create($data);
            $user->roles()->sync($request->validated('roles'));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Пользователь создан.']);

        return to_route('system.users.index');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('system/users/Form', [
            'user' => $user->load('roles:id,name,code'),
            'roles' => $this->roles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except('roles');

        if (empty($data['password'])) {
            unset($data['password']);
        }

        DB::transaction(function () use ($user, $data, $request): void {
            $user->update($data);
            $user->roles()->sync($request->validated('roles'));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Пользователь обновлён.']);

        return to_route('system.users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Пользователь удалён.']);

        return to_route('system.users.index');
    }

    /** @return array<int, Role> */
    private function roles(): array
    {
        return Role::query()
            ->when(! request()->user()->isSuperAdmin(), fn ($query) => $query->where('code', '!=', Role::SUPER_ADMIN))
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->all();
    }
}
