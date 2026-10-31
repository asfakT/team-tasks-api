<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\TaskController;

Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::middleware('throttle:6,1')->group(function () {
            Route::post('/register', 'register')->name('auth.register');
            Route::post('/login', 'login')->name('auth.login');
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', 'me')->name('auth.me');
            Route::post('/logout', 'logout')->name('auth.logout');
            Route::apiResource('teams', TeamController::class);
            Route::get('/teams/{team}/members', [TeamMemberController::class, 'index'])->name('teams.members.index');
            Route::post('/teams/{team}/members', [TeamMemberController::class, 'store'])->name('teams.members.store');
            Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('teams.members.destroy');
            Route::apiResource('teams.tasks', TaskController::class)->scoped();
            Route::post('/teams/{team}/tasks/{task}/complete', [TaskController::class, 'complete'])->scopeBindings()->name('teams.tasks.complete');
        });
    });
});
