<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * B11 — Auth middleware regression sweep: every protected route in
 * routes/api.php must return 401 without a valid Sanctum token. Placed
 * last in Step B because it is cross-cutting and sweeps every route
 * exercised by B1-B10, so it is most useful as a final pass once every
 * other module's routes are already known-good.
 */
class AuthMiddlewareRegressionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function protectedRoutesProvider(): array
    {
        return [
            'auth.logout' => ['POST', '/api/auth/logout'],
            'auth.me' => ['GET', '/api/auth/me'],

            'dashboard.stats' => ['GET', '/api/dashboard/stats'],

            'shipments.index' => ['GET', '/api/shipments'],
            'shipments.show' => ['GET', '/api/shipments/1'],
            'shipments.store' => ['POST', '/api/shipments'],
            'shipments.update' => ['PUT', '/api/shipments/1'],
            'shipments.destroy' => ['DELETE', '/api/shipments/1'],
            'shipments.push-inbound' => ['POST', '/api/shipments/1/push-inbound'],

            'inbound.index' => ['GET', '/api/inbound'],
            'inbound.show' => ['GET', '/api/inbound/1'],
            'inbound.store' => ['POST', '/api/inbound'],
            'inbound.update' => ['PUT', '/api/inbound/1'],
            'inbound.destroy' => ['DELETE', '/api/inbound/1'],
            'inbound.details' => ['GET', '/api/inbound/1/details'],

            'outbound.index' => ['GET', '/api/outbound'],
            'outbound.show' => ['GET', '/api/outbound/1'],
            'outbound.store' => ['POST', '/api/outbound'],
            'outbound.update' => ['PUT', '/api/outbound/1'],
            'outbound.destroy' => ['DELETE', '/api/outbound/1'],

            'reports.shipment' => ['GET', '/api/reports/shipment'],
            'reports.inbound' => ['GET', '/api/reports/inbound'],
            'reports.outbound' => ['GET', '/api/reports/outbound'],
            'reports.inventory' => ['GET', '/api/reports/inventory'],

            'master.locations' => ['GET', '/api/master/locations'],
            'master.categories' => ['GET', '/api/master/categories'],
            'master.recipients' => ['GET', '/api/master/recipients'],

            'master.apk.index' => ['GET', '/api/master/apk'],
            'master.apk.store' => ['POST', '/api/master/apk'],
            'master.apk.update' => ['PUT', '/api/master/apk/1'],
            'master.apk.reset-password' => ['POST', '/api/master/apk/1/reset-password'],
            'master.apk.logout' => ['POST', '/api/master/apk/1/logout'],
            'master.apk.destroy' => ['DELETE', '/api/master/apk/1'],

            'moving.index' => ['GET', '/api/moving'],
            'moving.store' => ['POST', '/api/moving'],
            'moving.destroy' => ['DELETE', '/api/moving/1'],

            'monitoring.index' => ['GET', '/api/monitoring'],

            'admin.users.index' => ['GET', '/api/admin/users'],
            'admin.users.store' => ['POST', '/api/admin/users'],
            'admin.users.update' => ['PUT', '/api/admin/users/1'],
            'admin.users.reset-password' => ['POST', '/api/admin/users/1/reset-password'],
            'admin.users.destroy' => ['DELETE', '/api/admin/users/1'],
        ];
    }

    #[DataProvider('protectedRoutesProvider')]
    public function test_protected_route_returns_401_without_token(string $method, string $uri): void
    {
        $response = $this->json($method, $uri);

        $response->assertStatus(401);
    }

    public function test_login_route_does_not_require_authentication(): void
    {
        // Sanity check: the one route that should NOT 401 without a token
        // (it returns a validation error instead, since no credentials
        // were supplied).
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422);
    }
}
