<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BusinessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isInternal();
    }

    public function view(User $user, Model $model): bool
    {
        return $user->isInternal();
    }

    public function create(User $user): bool
    {
        return $user->isInternal();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->isInternal();
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->isInternal();
    }

    public function restore(User $user, Model $model): bool
    {
        return $user->isInternal();
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $user->role->value === 'admin';
    }
}
