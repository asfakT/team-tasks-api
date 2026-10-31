<?php

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTeamAction
{
    /**
     * Create a new class instance.
     */
    public function handle(User $owner, string $name): Team{
        return DB::transaction(function () use ($owner, $name) {
            $team = $owner->ownedTeams()->create(['name' => $name]);
            $team->members()->attach($owner->id);

            return $team;
        });
    }
}
