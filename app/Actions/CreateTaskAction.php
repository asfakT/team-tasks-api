<?php

namespace App\Actions;

use App\Models\Task;
use App\Models\Team;
use App\Models\User;

class CreateTaskAction
{
    public function handle(Team $team, User $creator, array $data): Task
    {
        return $team->tasks()->create([...$data, 'creator_id' => $creator->id]);
    }
}
