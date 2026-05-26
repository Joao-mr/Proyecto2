<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('user-list');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->checkPermissionTo('user-list');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('user-create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->checkPermissionTo('user-edit');
    }

    public function updateAvatar(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->checkPermissionTo('user-edit');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->checkPermissionTo('user-delete');
    }
}
