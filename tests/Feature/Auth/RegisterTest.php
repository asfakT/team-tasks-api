<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Asfak',
            'email' => 'asfak@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson(route('auth.register'), $this->payload());

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email', 'created_at'], 'token']])
            ->assertJsonMissingPath('data.user.password');

        $this->assertDatabaseHas('users', ['email' => 'asfak@test.com']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'asfak@test.com']);

        $this->postJson(route('auth.register'), $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_password_must_be_confirmed(): void
    {
        $this->postJson(route('auth.register'), $this->payload(['password_confirmation' => 'different']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }
}