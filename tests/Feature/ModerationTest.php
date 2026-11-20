<?php

namespace Tests\Feature;

use App\Enums\SondageStatus;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Validation ou refus des sondages, réservés aux administrateurs. */
class ModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_pending_surveys_and_can_open_any_survey(): void
    {
        $admin = User::factory()->role('administrateur')->create();
        $enAttente = Sondage::factory()->avecQuestions()->create(['titre' => 'À valider']);
        Sondage::factory()->statut(SondageStatus::VALIDE)->create(['titre' => 'Déjà validé']);

        $this->actingAs($admin)->get(route('moderation.index'))
            ->assertOk()
            ->assertSee('À valider')
            ->assertDontSee('Déjà validé');
        $this->actingAs($admin)->get(route('sondages.show', $enAttente))->assertOk()->assertSee('Valider');
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('En attente de validation');
    }

    public function test_admin_approves_a_pending_survey(): void
    {
        $admin = User::factory()->role('administrateur')->create();
        $sondage = Sondage::factory()->create();

        $this->actingAs($admin)->post(route('moderation.approve', $sondage))->assertRedirect()->assertSessionHas('success');

        $sondage->refresh();
        $this->assertSame(SondageStatus::VALIDE, $sondage->statut);
        $this->assertSame($admin->id, $sondage->moderateur_id);
        $this->assertNotNull($sondage->modere_le);
    }

    public function test_admin_rejects_a_survey_with_a_reason_shown_to_its_owner(): void
    {
        $admin = User::factory()->role('administrateur')->create();
        $owner = User::factory()->role('client')->create();
        $sondage = Sondage::factory()->for($owner)->avecQuestions()->create();

        $this->actingAs($admin)
            ->post(route('moderation.reject', $sondage), ['motif_refus' => 'Les questions sont trop vagues.'])
            ->assertRedirect();

        $sondage->refresh();
        $this->assertSame(SondageStatus::REFUSE, $sondage->statut);
        $this->assertSame('Les questions sont trop vagues.', $sondage->motif_refus);

        $this->actingAs($owner)->get(route('sondages.show', $sondage))
            ->assertOk()
            ->assertSee('Refusé')
            ->assertSee('Les questions sont trop vagues.');
    }

    public function test_a_rejection_requires_a_reason(): void
    {
        $admin = User::factory()->role('administrateur')->create();
        $sondage = Sondage::factory()->create();

        $this->actingAs($admin)
            ->post(route('moderation.reject', $sondage), ['motif_refus' => ''])
            ->assertSessionHasErrorsIn('refus'.$sondage->id, 'motif_refus');

        $this->assertSame(SondageStatus::EN_ATTENTE, $sondage->fresh()->statut);
    }

    public function test_owner_without_admin_role_cannot_approve_or_reject_their_own_survey(): void
    {
        $owner = User::factory()->role('client')->create();
        $sondage = Sondage::factory()->for($owner)->create();

        $this->actingAs($owner)->get(route('moderation.index'))->assertForbidden();
        $this->actingAs($owner)->post(route('moderation.approve', $sondage))->assertForbidden();
        $this->actingAs($owner)->post(route('moderation.reject', $sondage), ['motif_refus' => 'x'])->assertForbidden();
        $this->actingAs($owner)->get(route('sondages.show', $sondage))->assertOk()->assertDontSee('Confirmer le refus');

        $this->assertSame(SondageStatus::EN_ATTENTE, $sondage->fresh()->statut);
    }

    public function test_other_roles_cannot_moderate(): void
    {
        $sondage = Sondage::factory()->create();

        foreach (['client', 'commercial', 'sondeur'] as $role) {
            $user = User::factory()->role($role)->create();
            $this->actingAs($user)->post(route('moderation.approve', $sondage))->assertForbidden();
        }

        $this->assertSame(SondageStatus::EN_ATTENTE, $sondage->fresh()->statut);
    }

    public function test_an_administrator_may_approve_their_own_survey(): void
    {
        $admin = User::factory()->role('administrateur')->create();
        $sondage = Sondage::factory()->for($admin)->create();

        $this->actingAs($admin)->post(route('moderation.approve', $sondage))->assertRedirect();

        $this->assertSame(SondageStatus::VALIDE, $sondage->fresh()->statut);
    }

    public function test_only_pending_surveys_can_be_moderated(): void
    {
        $admin = User::factory()->role('administrateur')->create();

        foreach ([SondageStatus::VALIDE, SondageStatus::REFUSE, SondageStatus::EN_COURS, SondageStatus::TERMINE] as $statut) {
            $sondage = Sondage::factory()->statut($statut)->create();

            $this->actingAs($admin)->post(route('moderation.approve', $sondage))->assertForbidden();
            $this->actingAs($admin)->post(route('moderation.reject', $sondage), ['motif_refus' => 'x'])->assertForbidden();
            $this->assertSame($statut, $sondage->fresh()->statut);
        }
    }
}
