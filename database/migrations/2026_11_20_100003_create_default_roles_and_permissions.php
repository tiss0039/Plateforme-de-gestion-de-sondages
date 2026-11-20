<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crée les rôles et permissions nécessaires au fonctionnement de
 * l'application s'ils n'existent pas encore (firstOrCreate) : une simple
 * migration suffit, avec ou sans seeders. Les rôles et permissions déjà
 * présents sont conservés ; des permissions sont seulement ajoutées.
 */
return new class extends Migration
{
    private const PERMISSIONS_PAR_ROLE = [
        'administrateur' => [
            'create-sondage', 'edit-sondage', 'delete-sondage', 'assign-sondage',
            'execute-sondage', 'view-resultats', 'manage-users', 'validate-sondage',
        ],
        'client' => ['create-sondage', 'execute-sondage'],
        'commercial' => ['assign-sondage', 'execute-sondage'],
        'sondeur' => ['execute-sondage'],
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS_PAR_ROLE as $nomRole => $permissions) {
            $role = Role::firstOrCreate(['name' => $nomRole, 'guard_name' => 'web']);

            foreach ($permissions as $nomPermission) {
                $permission = Permission::firstOrCreate(['name' => $nomPermission, 'guard_name' => 'web']);

                if (! $role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Les rôles et permissions peuvent être attribués à des utilisateurs :
     * ils ne sont pas supprimés lors d'un retour arrière.
     */
    public function down(): void {}
};
