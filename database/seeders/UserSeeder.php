<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Comptes de démonstration, destinés au développement local uniquement
 * (mot de passe : « password »).
 */
class UserSeeder extends Seeder
{
    private const COMPTES = [
        ['Admin', 'admin@example.com', 'administrateur'],
        ['Client Test', 'client@example.com', 'client'],
        ['Commercial Test', 'commercial@example.com', 'commercial'],
        ['Sondeur Test', 'sondeur@example.com', 'sondeur'],
    ];

    public function run(): void
    {
        foreach (self::COMPTES as [$nom, $email, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $nom, 'password' => Hash::make('password'), 'email_verified_at' => now()],
            );

            $user->syncRoles([$role]);
        }
    }
}
