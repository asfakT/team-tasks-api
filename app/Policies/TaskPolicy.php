<?php

namespace App\Policies;

use App\Models\Task;
use Illuminate\Auth\Access\Response;
use App\Models\Team;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $task->team->hasMember($user);
    }

    public function create(User $user, Team $team): bool
    {
        return $team->hasMember($user);
    }

    public function update(User $user, Task $task): bool
    {
        return $task->team->hasMember($user)
            && ($task->creator_id === $user->id || $task->team->isOwner($user));
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function complete(User $user, Task $task): bool
    {
        return $task->assignee_id === $user->id;
    }
}
