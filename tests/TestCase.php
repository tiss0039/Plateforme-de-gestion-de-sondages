<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Garde-fou exécuté avant les traits de test (RefreshDatabase,
     * DatabaseMigrations, DatabaseTruncation…), donc avant toute
     * réinitialisation de la base : les tests ne doivent jamais s'exécuter
     * sur une autre base que la base SQLite en mémoire définie dans phpunit.xml.
     */
    protected function setUpTraits()
    {
        $this->ensureTestDatabaseIsInMemorySqlite();

        return parent::setUpTraits();
    }

    private function ensureTestDatabaseIsInMemorySqlite(): void
    {
        if ($this->app->configurationIsCached()) {
            throw new RuntimeException(
                'Tests interrompus avant toute opération sur la base : la configuration Laravel est en cache '
                .'(bootstrap/cache/config.php) et les réglages de phpunit.xml seraient ignorés. '
                .'Exécutez « php artisan config:clear » puis relancez les tests.'
            );
        }

        $default = config('database.default');
        $connection = config("database.connections.{$default}", []);

        if (($connection['driver'] ?? null) !== 'sqlite'
            || ($connection['database'] ?? null) !== ':memory:'
            || ! empty($connection['url'])) {
            throw new RuntimeException(sprintf(
                'Tests interrompus avant toute opération sur la base : la connexion « %s » (pilote « %s », base « %s »%s) '
                .'n\'est pas la base SQLite en mémoire prévue par phpunit.xml.',
                $default,
                $connection['driver'] ?? '?',
                $connection['database'] ?? '?',
                empty($connection['url']) ? '' : ', URL de connexion DB_URL définie'
            ));
        }
    }
}
