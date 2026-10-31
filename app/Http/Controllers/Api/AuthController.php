<?php

namespace App\Http\Controllers\Api;

use App\Actions\LoginUserAction;
use App\Actions\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->handle($request->validated());
        
        return $this->respondWithToken($user, 201);
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $user = $action->handle($request->validated('email'), $request->validated('password'));

        return $this->respondWithToken($user);
    }

    public function me(Request $request):UserResource
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    private function respondWithToken(User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $user->createToken('api')->plainTextToken,
            ],
        ], $status);
    }
}
