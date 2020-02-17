<?php

namespace Novatio\TranslationManager\Policies;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Novatio\Admin\Policies\AdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class TranslationPolicy
{
    use HandlesAuthorization;

    /**
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function update(User $user, Model $model)
    {
        return true;
    }

    /**
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function create(User $user, Model $model)
    {
        return false;
    }

    /**
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function delete(User $user, Model $model)
    {
        return false;
    }

    /**
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function view(User $user, Model $model)
    {
        return true;
    }
}
