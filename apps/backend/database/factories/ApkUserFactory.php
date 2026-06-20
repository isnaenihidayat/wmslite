<?php

namespace Database\Factories;

use App\Models\ApkUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<ApkUser>
 *
 * Covers both el_apk (main) and el_apk_s (staging) tables, since
 * ApkController exercises a raw-SQL union query across both tables and
 * there is no separate Eloquent model for el_apk_s (it is queried directly
 * via DB::table('el_apk_s') in ApkController).
 */
class ApkUserFactory extends Factory
{
    protected $model = ApkUser::class;

    /**
     * Default state: creates a row in el_apk (main table) via Eloquent.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => Hash::make('password'),
            'last_login' => null,
            'token' => null,
        ];
    }

    /**
     * Insert a matching row directly into el_apk_s (staging table), which
     * has no Eloquent model and is queried via DB::table('el_apk_s').
     *
     * @return array<string, mixed> the inserted attributes, including 'id'
     */
    public function createStaging(array $overrides = []): array
    {
        $attributes = array_merge([
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => Hash::make('password'),
            'last_login' => null,
            'token' => null,
        ], $overrides);

        $id = DB::table('el_apk_s')->insertGetId($attributes);

        return array_merge($attributes, ['id' => $id]);
    }
}
