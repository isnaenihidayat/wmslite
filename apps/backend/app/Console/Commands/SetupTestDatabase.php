<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Drops and recreates the dedicated `wmslite_test` MySQL database, then loads
 * the el_* schema from the repo-root tables_schema.sql (single source of truth).
 *
 * Per phase-01-test-infrastructure_PLAN_19-06-26.md Step A2a + execute-agent
 * instruction E5: the database name below is a LITERAL hard-coded string.
 * It must never be derived dynamically (e.g. from config('database.connections.mysql.database'))
 * because that value changes depending on which env file is loaded, and a
 * misconfigured environment could otherwise cause this destructive DROP
 * DATABASE statement to target the real `wmslite` database instead.
 */
class SetupTestDatabase extends Command
{
    /**
     * The literal, hard-coded test database name. Never make this dynamic.
     */
    private const TEST_DATABASE_NAME = 'wmslite_test';

    protected $signature = 'test:setup-db';

    protected $description = 'Drop/recreate the wmslite_test database and load the el_* schema from tables_schema.sql';

    public function handle(): int
    {
        $schemaPath = base_path('../../tables_schema.sql');

        if (! file_exists($schemaPath)) {
            $this->error("Schema file not found at: {$schemaPath}");

            return self::FAILURE;
        }

        $connectionDatabase = config('database.connections.mysql.database');

        if ($connectionDatabase !== self::TEST_DATABASE_NAME) {
            $this->error(
                "Refusing to run: configured DB_DATABASE ('{$connectionDatabase}') does not match ".
                "the expected literal test database name ('".self::TEST_DATABASE_NAME."'). ".
                'This guards against accidentally targeting the real wmslite database.'
            );

            return self::FAILURE;
        }

        $this->info('Dropping and recreating database: '.self::TEST_DATABASE_NAME);

        // Connect without selecting a database so DROP/CREATE DATABASE works
        // even on the very first run when wmslite_test does not exist yet.
        $pdo = DB::connection('mysql')->getPdo();
        $pdo->exec('DROP DATABASE IF EXISTS `'.self::TEST_DATABASE_NAME.'`');
        $pdo->exec('CREATE DATABASE `'.self::TEST_DATABASE_NAME.'`');
        $pdo->exec('USE `'.self::TEST_DATABASE_NAME.'`');

        $this->info('Loading schema from: '.realpath($schemaPath));

        $sql = file_get_contents($schemaPath);
        $pdo->exec($sql);

        $this->info('wmslite_test schema loaded successfully.');

        return self::SUCCESS;
    }
}
