<?php

namespace Tests\Feature;

use App\Models\Sondage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Rôles créés par les migrations et permissions associées. */
class RolesEtPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_create_the_roles_and_their_permissions(): void
    {
        $this->assertSame(
            ['administrateur', 'client', 'commercial', 'sondeur'],
            Role::orderBy('name')->pluck('name')->all()
        );

        $admin = Role::findByName('administrateur');
        $this->assertTrue($admin->hasPermissionTo('validate-sondage'));
        $this->assertTrue($admin->hasPermissionTo('view-resultats'));
        $this->assertTrue(Role::findByName('client')->hasPermissionTo('create-sondage'));
        $this->assertFalse(Role::findByName('client')->hasPermissionTo('validate-sondage'));

        foreach (['administrateur', 'client', 'commercial', 'sondeur'] as $role) {
            $this->assertTrue(Role::findByName($role)->hasPermissionTo('execute-sondage'), $role);
        }
    }

    public function test_the_former_response_tables_are_kept(): void
    {
        // Les migrations ajoutées sont additives : les tables d'origine restent en place.
        $this->assertTrue(Schema::hasTable('reponses'));
        $this->assertTrue(Schema::hasTable('stockage_reponses'));
        $this->assertTrue(Schema::hasTable('participations'));
        $this->assertTrue(Schema::hasTable('participation_reponses'));
    }

    public function test_a_new_account_gets_the_client_role_and_can_create_surveys(): void
    {
        $this->post(route('register'), [
            'name' => 'Nouvel utilisateur',
            'email' => 'nouveau@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'nouveau@example.com')->sole();
        $this->assertTrue($user->hasRole('client'));
        $this->actingAs($user)->get(route('sondages.create'))->assertOk();
    }

    public function test_only_roles_with_the_create_permission_can_create_surveys(): void
    {
        foreach (['administrateur' => 200, 'client' => 200, 'commercial' => 403, 'sondeur' => 403] as $role => $statut) {
            $user = User::factory()->role($role)->create();
            $this->actingAs($user)->get(route('sondages.create'))->assertStatus($statut);
        }

        $sansRole = User::factory()->create();
        $this->actingAs($sansRole)->get(route('sondages.create'))->assertForbidden();
        $this->actingAs($sansRole)->post(route('sondages.store'), ['titre' => 'Test'])->assertForbidden();
        Sondage::factory()->enCours()->create();
        $this->actingAs($sansRole)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('pas d\'en créer', false)
            ->assertViewHas('sondagesOuverts', 0);
        $this->actingAs(User::factory()->role('sondeur')->create())->get(route('dashboard'))
            ->assertViewHas('sondagesOuverts', 1);

        $this->assertSame(1, Sondage::count());
    }

    public function test_only_administrators_see_the_validation_menu(): void
    {
        $this->actingAs(User::factory()->role('administrateur')->create())
            ->get(route('dashboard'))->assertSee(route('moderation.index'));
        $this->actingAs(User::factory()->role('client')->create())
            ->get(route('dashboard'))->assertDontSee(route('moderation.index'));
    }
}
