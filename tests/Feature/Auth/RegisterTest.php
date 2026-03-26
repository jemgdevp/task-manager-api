<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_email_verification_flow_fields(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('email_verification_required', true);
        $response->assertJsonPath('user.email', 'test@example.com');
        $response->assertJsonPath('user.email_verified', false);
        $response->assertJsonStructure([
            'message',
            'email_verification_required',
            'user' => ['id', 'name', 'email', 'email_verified'],
            'access_token',
            'token_type',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);
    }
}
