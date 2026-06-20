<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * SCOPE NOTE (per plan Step B8): the existing admin check in
 * UserController is ad hoc — a repeated `if (! $request->user()->admin)`
 * guard in every method, not a formal Laravel Policy/Gate. This test suite
 * verifies the ad hoc check AS CURRENTLY IMPLEMENTED. Introducing a
 * Policy/Gate is explicitly out of this phase's scope — that refactor is
 * Phase 2 work.
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    protected function actingNonAdmin(): User
    {
        $user = User::factory()->create(['admin' => 0]);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_index_returns_users_for_admin(): void
    {
        $this->actingAdmin();

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_index_returns_403_for_non_admin(): void
    {
        $this->actingNonAdmin();

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_index_filters_by_search(): void
    {
        $this->actingAdmin();

        User::factory()->create(['first_name' => 'Findme', 'last_name' => 'Person']);
        User::factory()->create(['first_name' => 'Other', 'last_name' => 'Person']);

        $response = $this->getJson('/api/admin/users?search=Findme');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('first_name')->all();
        $this->assertContains('Findme', $names);
        $this->assertNotContains('Other', $names);
    }

    public function test_store_creates_user_for_admin(): void
    {
        $this->actingAdmin();

        $payload = [
            'first_name' => 'New',
            'last_name' => 'User',
            'email_address' => 'newuser@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/admin/users', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.email_address', 'newuser@example.com')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.admin', false);

        $this->assertDatabaseHas('el_user', ['email_address' => 'newuser@example.com']);
    }

    public function test_store_returns_403_for_non_admin(): void
    {
        $this->actingNonAdmin();

        $response = $this->postJson('/api/admin/users', [
            'first_name' => 'New',
            'last_name' => 'User',
            'email_address' => 'blocked@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('el_user', ['email_address' => 'blocked@example.com']);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        $this->actingAdmin();

        User::factory()->create(['email_address' => 'dupe@example.com']);

        $response = $this->postJson('/api/admin/users', [
            'first_name' => 'Dupe',
            'last_name' => 'User',
            'email_address' => 'dupe@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email_address']);
    }

    public function test_update_modifies_user_for_admin(): void
    {
        $this->actingAdmin();

        $user = User::factory()->create(['first_name' => 'Old']);

        $response = $this->putJson("/api/admin/users/{$user->user_id}", [
            'first_name' => 'New',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.first_name', 'New');
    }

    public function test_update_returns_403_for_non_admin(): void
    {
        $this->actingNonAdmin();

        $target = User::factory()->create();

        $response = $this->putJson("/api/admin/users/{$target->user_id}", [
            'first_name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_reset_password_updates_hash_for_admin(): void
    {
        $this->actingAdmin();

        $user = User::factory()->create();

        $response = $this->postJson("/api/admin/users/{$user->user_id}/reset-password", [
            'password' => 'newpassword123',
        ]);

        $response->assertOk();

        $updated = User::find($user->user_id);
        $this->assertTrue(Hash::check('newpassword123', $updated->password));
    }

    public function test_reset_password_returns_403_for_non_admin(): void
    {
        $this->actingNonAdmin();

        $target = User::factory()->create();

        $response = $this->postJson("/api/admin/users/{$target->user_id}/reset-password", [
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(403);
    }

    public function test_destroy_deletes_user_for_admin(): void
    {
        $this->actingAdmin();

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/admin/users/{$user->user_id}");

        $response->assertOk();

        $this->assertDatabaseMissing('el_user', ['user_id' => $user->user_id]);
    }

    public function test_destroy_returns_403_for_non_admin(): void
    {
        $this->actingNonAdmin();

        $target = User::factory()->create();

        $response = $this->deleteJson("/api/admin/users/{$target->user_id}");

        $response->assertStatus(403);
    }

    public function test_destroy_rejects_self_deletion(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->deleteJson("/api/admin/users/{$admin->user_id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('el_user', ['user_id' => $admin->user_id]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401);
    }
}
