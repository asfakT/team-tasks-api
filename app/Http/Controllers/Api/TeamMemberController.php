<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\AddMemberAction;
use App\Actions\RemoveMemberAction;
use App\Http\Requests\AddMemberRequest;
use App\Http\Resources\UserResource;
use App\Models\Team;
use App\Models\User;

class TeamMemberController extends Controller
{
    public function index(Team $team)
    {
        $this->authorize('view', $team);

        return UserResource::collection($team->members()->paginate(15));
    }

    public function store(AddMemberRequest $request, Team $team, AddMemberAction $action)
    {
        $this->authorize('manageMembers', $team);

        $user = $action->handle($team, $request->validated('email'));

        return UserResource::make($user)->response()->setStatusCode(201);
    }

    public function destroy(Team $team, User $user, RemoveMemberAction $action)
    {
        $this->authorize('manageMembers', $team);

        $action->handle($team, $user);

        return response()->noContent();
    }
}
