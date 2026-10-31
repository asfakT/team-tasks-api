<?php

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddMemberAction
{
    /**
     * Create a new class instance.
     */
    public function handle(Team $team, string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($team->hasMember($user)) {
            throw ValidationException::withMessages([
                'email' => ['This user is already a member.'],
            ]);
        }

        $team->members()->attach($user->id);

        return $user;
    }
}
