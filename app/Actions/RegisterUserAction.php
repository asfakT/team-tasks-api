<?php

namespace App\Actions;
use App\Models\User;

class RegisterUserAction
{
    /**
     * Create a new class instance.
     */
    public function handle(array $data): User
    {
        return User::create($data);
    }
}
