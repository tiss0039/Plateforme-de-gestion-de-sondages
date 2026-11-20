<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_one_demo_account_per_role(): void
    {
        $this->seed();
        $this->seed(); // relancer le seeder ne crée pas de doublon

        $comptes = [
            'admin@example.com' => 'administrateur',
            'client@example.com' => 'client',
            'commercial@example.com' => 'commercial',
            'sondeur@example.com' => 'sondeur',
        ];

        foreach ($comptes as $email => $role) {
            $user = User::where('email', $email)->sole();
            $this->assertTrue(Hash::check('password', $user->password));
            $this->assertSame([$role], $user->getRoleNames()->all());
        }
        $this->assertSame(4, User::count());
    }
}
