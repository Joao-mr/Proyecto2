<?php
namespace App\Policies;

use App\Models\User;
use App\Models\SalaCategoria;

class SalaCategoriaPolicy
{
    /**
     * Determine whether the user can view any sala-categoria relations.
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('sala-categoria-view');
    }

    /**
     * Determine whether the user can view a specific sala-categoria relation.
     */
    public function view(User $user, SalaCategoria $salaCategoria)
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('sala-categoria-view');
    }

    /**
     * Determine whether the user can create sala-categoria relations.
     */
    public function create(User $user)
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('sala-categoria-create');
    }

    /**
     * Determine whether the user can update a sala-categoria relation.
     */
    public function update(User $user, SalaCategoria $salaCategoria)
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('sala-categoria-edit');
    }

    /**
     * Determine whether the user can delete a sala-categoria relation.
     */
    public function delete(User $user, SalaCategoria $salaCategoria)
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('sala-categoria-delete');
    }
}
