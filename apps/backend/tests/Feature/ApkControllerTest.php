<?php

namespace Tests\Feature;

use App\Models\ApkUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers both el_apk (main) and el_apk_s (staging) via the
 * ApkUserFactory built in Step A3. Per PVL instruction E7, assertions
 * must cover the actual raw-SQL-derived response fields (`is_logged_in`,
 * `source_table`), not just HTTP status codes — this is the one test that
 * proves the Step A1 MySQL-vs-SQLite test-DB decision was correct (the
 * union query + CASE WHEN raw SQL is MySQL-dialect-specific).
 */
class ApkControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_index_returns_union_of_main_and_staging_with_source_table_and_is_logged_in(): void
    {
        $this->actingUser();

        ApkUser::factory()->create(['name' => 'Main Scanner', 'username' => 'main_scanner', 'token' => 'sometoken']);
        ApkUser::factory()->createStaging(['name' => 'Staging Scanner', 'username' => 'staging_scanner', 'token' => null]);

        $response = $this->getJson('/api/master/apk');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'username', 'last_login', 'is_logged_in', 'source_table']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);

        $this->assertSame(2, $response->json('meta.total'));

        $rows = collect($response->json('data'))->keyBy('username');

        // Raw-SQL-derived field assertions (E7): is_logged_in and
        // source_table come from the CASE WHEN / literal-label raw SQL in
        // ApkController::index(), not from any Eloquent attribute.
        $mainRow = $rows['main_scanner'];
        $this->assertSame(1, (int) $mainRow['is_logged_in']);
        $this->assertSame('main', $mainRow['source_table']);

        $stagingRow = $rows['staging_scanner'];
        $this->assertSame(0, (int) $stagingRow['is_logged_in']);
        $this->assertSame('staging', $stagingRow['source_table']);
    }

    public function test_index_filters_by_table_main_only(): void
    {
        $this->actingUser();

        ApkUser::factory()->create(['username' => 'main_only']);
        ApkUser::factory()->createStaging(['username' => 'staging_only']);

        $response = $this->getJson('/api/master/apk?table=main');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('main', $response->json('data.0.source_table'));
    }

    public function test_index_filters_by_table_staging_only(): void
    {
        $this->actingUser();

        ApkUser::factory()->create(['username' => 'main_only']);
        ApkUser::factory()->createStaging(['username' => 'staging_only']);

        $response = $this->getJson('/api/master/apk?table=staging');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('staging', $response->json('data.0.source_table'));
    }

    public function test_index_filters_by_search(): void
    {
        $this->actingUser();

        ApkUser::factory()->create(['name' => 'Findme Scanner', 'username' => 'findme']);
        ApkUser::factory()->create(['name' => 'Other Scanner', 'username' => 'other']);

        $response = $this->getJson('/api/master/apk?search=Findme');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
    }

    public function test_store_creates_account_in_main_table_by_default(): void
    {
        $this->actingUser();

        $response = $this->postJson('/api/master/apk', [
            'name' => 'New Scanner',
            'username' => 'new_scanner',
            'password' => 'pass1234',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.username', 'new_scanner')
            ->assertJsonPath('data.is_logged_in', false)
            ->assertJsonPath('data.source_table', 'main');

        $this->assertDatabaseHas('el_apk', ['username' => 'new_scanner']);
    }

    public function test_store_creates_account_in_staging_table_when_requested(): void
    {
        $this->actingUser();

        $response = $this->postJson('/api/master/apk', [
            'name' => 'New Staging Scanner',
            'username' => 'new_staging_scanner',
            'password' => 'pass1234',
            'table' => 'staging',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.source_table', 'staging');

        $this->assertDatabaseHas('el_apk_s', ['username' => 'new_staging_scanner']);
    }

    public function test_store_rejects_duplicate_username_in_main_table(): void
    {
        $this->actingUser();

        ApkUser::factory()->create(['username' => 'dupe_scanner']);

        $response = $this->postJson('/api/master/apk', [
            'name' => 'Dupe',
            'username' => 'dupe_scanner',
            'password' => 'pass1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_update_modifies_main_table_account(): void
    {
        $this->actingUser();

        $apk = ApkUser::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/master/apk/{$apk->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.source_table', 'main');

        $this->assertDatabaseHas('el_apk', ['id' => $apk->id, 'name' => 'New Name']);
    }

    public function test_update_modifies_staging_table_account(): void
    {
        $this->actingUser();

        $staging = ApkUser::factory()->createStaging(['name' => 'Old Staging Name']);

        $response = $this->putJson("/api/master/apk/{$staging['id']}?table=staging", [
            'name' => 'New Staging Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Staging Name')
            ->assertJsonPath('data.source_table', 'staging');

        $this->assertDatabaseHas('el_apk_s', ['id' => $staging['id'], 'name' => 'New Staging Name']);
    }

    public function test_update_returns_404_for_missing_account(): void
    {
        $this->actingUser();

        $response = $this->putJson('/api/master/apk/999999', ['name' => 'X']);

        $response->assertStatus(404);
    }

    public function test_reset_password_clears_token_and_updates_password(): void
    {
        $this->actingUser();

        $apk = ApkUser::factory()->create(['token' => 'oldtoken']);

        $response = $this->postJson("/api/master/apk/{$apk->id}/reset-password", [
            'password' => 'newpass123',
        ]);

        $response->assertOk();

        $updated = DB::table('el_apk')->where('id', $apk->id)->first();
        $this->assertNull($updated->token);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpass123', $updated->password));
    }

    public function test_reset_password_returns_404_for_missing_account(): void
    {
        $this->actingUser();

        $response = $this->postJson('/api/master/apk/999999/reset-password', [
            'password' => 'newpass123',
        ]);

        $response->assertStatus(404);
    }

    public function test_force_logout_clears_token(): void
    {
        $this->actingUser();

        $apk = ApkUser::factory()->create(['token' => 'activetoken']);

        $response = $this->postJson("/api/master/apk/{$apk->id}/logout");

        $response->assertOk();

        $updated = DB::table('el_apk')->where('id', $apk->id)->first();
        $this->assertNull($updated->token);
    }

    public function test_force_logout_returns_404_for_missing_account(): void
    {
        $this->actingUser();

        $response = $this->postJson('/api/master/apk/999999/logout');

        $response->assertStatus(404);
    }

    public function test_destroy_deletes_main_table_account(): void
    {
        $this->actingUser();

        $apk = ApkUser::factory()->create();

        $response = $this->deleteJson("/api/master/apk/{$apk->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('el_apk', ['id' => $apk->id]);
    }

    public function test_destroy_deletes_staging_table_account(): void
    {
        $this->actingUser();

        $staging = ApkUser::factory()->createStaging();

        $response = $this->deleteJson("/api/master/apk/{$staging['id']}?table=staging");

        $response->assertOk();

        $this->assertDatabaseMissing('el_apk_s', ['id' => $staging['id']]);
    }

    public function test_destroy_returns_404_for_missing_account(): void
    {
        $this->actingUser();

        $response = $this->deleteJson('/api/master/apk/999999');

        $response->assertStatus(404);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/master/apk');

        $response->assertStatus(401);
    }
}
