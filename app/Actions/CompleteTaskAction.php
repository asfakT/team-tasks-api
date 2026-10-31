<?php

namespace App\Actions;

use App\Enums\TaskStatus;
use App\Models\Task;

class CompleteTaskAction
{
    public function handle(Task $task): Task
    {
        $task->update(['status' => TaskStatus::Done]);

        return $task;
    }
}
