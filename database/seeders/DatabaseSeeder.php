<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        //cree permission
        Permission::create(['name' => 'create-sondage']);
        Permission::create(['name' => 'edit-sondage']);
        Permission::create(['name' => 'delete-sondage']);
        Permission::create(['name' => 'assign-sondage']);
        Permission::create(['name' => 'execute-sondage']);
        Permission::create(['name' => 'view-resultats']);
        Permission::create(['name' => 'manage-users']);

        //creer role
        $admin = Role::create(['name' => 'administrateur']);
        $commercial = Role::create(['name' => 'commercial']);
        $client = Role::create(['name' => 'client']);
        $sondeur = Role::create(['name' => 'sondeur']);

        //assigne permission
        $admin->givePermissionTo(['create-sondage', 'edit-sondage', 'delete-sondage', 'assign-sondage', 'view-resultats', 'manage-users']);
        $commercial->givePermissionTo(['assign-sondage']);
        $client->givePermissionTo(['create-sondage']);
        $sondeur->givePermissionTo(['execute-sondage']);
    }
}
