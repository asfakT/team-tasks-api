<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\Team;
use App\Http\Requests\UpdateTaskRequest;
use App\Actions\CompleteTaskAction;

class TaskController extends Controller
{
    public function index(Team $team)
    {
        $this->authorize('view', $team);

        return TaskResource::collection($team->tasks()->paginate(15));
    }

    public function store(StoreTaskRequest $request, Team $team, CreateTaskAction $action)
    {
        $this->authorize('create', [Task::class, $team]);

        $task = $action->handle($team, $request->user(), $request->validated());

        return TaskResource::make($task)->response()->setStatusCode(201);
    }

    public function show(Team $team, Task $task)
    {
        $this->authorize('view', $task);

        return TaskResource::make($task);
    }

    public function update(UpdateTaskRequest $request, Team $team, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return TaskResource::make($task);
    }

    public function destroy(Team $team, Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.'], 200);
    }

    public function complete(Team $team, Task $task, CompleteTaskAction $action)
    {
        $this->authorize('complete', $task);

        return TaskResource::make($action->handle($task));
    }
}
