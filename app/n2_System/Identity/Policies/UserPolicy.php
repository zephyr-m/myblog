<?php

namespace App\n2_System\Identity\Policies;

use App\n2_System\Identity\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canDo('user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->canDo('user.view');
    }

    public function create(User $user): bool
    {
        return $user->canDo('user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->canDo('user.update');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->canDo('user.delete') && ! $user->is($model);
    }
}
