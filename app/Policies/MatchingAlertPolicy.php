<?php

namespace App\Policies;

use App\Models\MatchingAlert;
use App\Models\User;

class MatchingAlertPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MatchingAlert $alert): bool
    {
        return $alert->savedSearch && $alert->savedSearch->user_id === $user->id;
    }

    public function update(User $user, MatchingAlert $alert): bool
    {
        return $alert->savedSearch && $alert->savedSearch->user_id === $user->id;
    }

    public function delete(User $user, MatchingAlert $alert): bool
    {
        return $alert->savedSearch && $alert->savedSearch->user_id === $user->id;
    }
}
