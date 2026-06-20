<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_returns_token_and_formatted_user(): void
    {
        $user = User::factory()->create([
            'email_address' => 'login@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => [
                    'id', 'name', 'first_name', 'last_name', 'email',
                    'mobile', 'status', 'is_admin', 'module', 'type', 'last_login',
                ],
            ])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->user_id)
            ->assertJsonPath('user.email', 'login@example.com')
            ->assertJsonPath('user.is_admin', false);

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_updates_last_login_timestamp(): void
    {
        $user = User::factory()->create([
            'email_address' => 'lastlogin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'last_login' => null,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'lastlogin@example.com',
            'password' => 'password123',
        ])->assertOk();

        $this->assertNotNull($user->fresh()->last_login);
    }

    public function test_login_revokes_old_tokens_on_new_login(): void
    {
        $user = User::factory()->create([
            'email_address' => 'revoke@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $oldToken = $user->createToken('old-token')->plainTextToken;
        $this->assertSame(1, $user->tokens()->count());

        $this->postJson('/api/auth/login', [
            'email' => 'revoke@example.com',
            'password' => 'password123',
        ])->assertOk();

        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_login_wrong_password_returns_validation_error(): void
    {
        User::factory()->create([
            'email_address' => 'wrongpass@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('correctpassword'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'wrongpass@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_unknown_email_returns_validation_error(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'doesnotexist@example.com',
            'password' => 'whatever123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_inactive_user_returns_403(): void
    {
        User::factory()->inactive()->create([
            'email_address' => 'inactive@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Your account is inactive. Please contact admin.');
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    public function test_me_returns_expected_shape(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonStructure([
                'user' => [
                    'id', 'name', 'first_name', 'last_name', 'email',
                    'mobile', 'status', 'is_admin', 'module', 'type', 'last_login',
                ],
            ])
            ->assertJsonPath('user.id', $user->user_id)
            ->assertJsonPath('user.is_admin', true);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
