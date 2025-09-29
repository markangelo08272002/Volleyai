<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VolleyballSession;
use Illuminate\Auth\Access\HandlesAuthorization;

class VolleyballSessionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\VolleyballSession  $volleyballSession
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, VolleyballSession $volleyballSession)
    {
        return $user->id === $volleyballSession->user_id;
    }
}
