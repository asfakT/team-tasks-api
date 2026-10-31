<?php

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveMemberAction
{
    /**
     * Create a new class instance.
     */
    public function handle(Team $team, User $user): void
    {
        if ($team->isOwner($user)) {
            throw ValidationException::withMessages([
                'user' => ['The owner cannot be removed.'],
            ]);
        }

        $team->members()->detach($user->id);
    }
}
