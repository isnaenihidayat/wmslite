<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorsConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function actingUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_cors_allows_configured_frontend_origin(): void
    {
        $this->actingUser();

        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->getJson('/api/dashboard/stats');

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
    }

    public function test_cors_does_not_echo_wildcard_origin(): void
    {
        $this->actingUser();

        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->getJson('/api/dashboard/stats');

        $this->assertNotSame('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_cors_rejects_unconfigured_origin(): void
    {
        $this->actingUser();

        $response = $this->withHeaders([
            'Origin' => 'http://evil.example.com',
        ])->getJson('/api/dashboard/stats');

        $this->assertNotSame(
            'http://evil.example.com',
            $response->headers->get('Access-Control-Allow-Origin')
        );
    }
}
