<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Login successful');
        $response->assertJsonPath('user.id', $user->id);
        $response->assertJsonPath('token_type', 'Bearer');
        $response->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email', 'email_verified'],
            'access_token',
            'token_type',
        ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'invalid-password',
        ]);

        $response->assertUnauthorized();
        $response->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_logout_returns_success_response(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertOk();
        $response->assertJsonPath('message', 'Successfully logged out');
    }

    public function test_unverified_user_can_request_verification_notification(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/email/verification-notification');

        $response->assertOk();
        $response->assertJsonPath('message', 'Verification link sent.');
    }

    public function test_signed_verification_link_marks_user_as_verified(): void
    {
        config(['app.frontend_verify_email_url' => '']);

        $user = User::factory()->unverified()->create();

        $url = URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $response = $this->getJson($url);

        $response->assertOk();
        $response->assertJsonPath('status', 'verified');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_broadcast_auth_requires_authentication(): void
    {
        $response = $this->postJson('/api/broadcasting/auth', [
            'channel_name' => 'private-user.1',
            'socket_id' => '123.456',
        ]);

        $response->assertUnauthorized();
    }
}
