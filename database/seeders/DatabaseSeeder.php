<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Les rôles (administrateur, client, commercial, sondeur) et leurs
     * permissions sont créés par la migration
     * 2026_11_20_100003_create_default_roles_and_permissions ; ce seeder
     * crée les comptes de démonstration.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }
}
