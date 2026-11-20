<?php

namespace Tests\Feature;

use App\Enums\SondageStatus;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ouverture et clôture d'un sondage, et parcours complet de bout en bout. */
class CycleDeVieTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_opens_a_validated_survey(): void
    {
        $owner = User::factory()->role('client')->create();
        $sondage = Sondage::factory()->for($owner)->statut(SondageStatus::VALIDE)->create();

        $this->actingAs($owner)->get(route('sondages.show', $sondage))->assertSee('Ouvrir aux réponses');
        $this->actingAs($owner)->post(route('sondages.open', $sondage))->assertRedirect(route('sondages.show', $sondage));

        $this->assertSame(SondageStatus::EN_COURS, $sondage->fresh()->statut);
    }

    public function test_a_survey_cannot_be_opened_before_validation_or_twice(): void
    {
        $owner = User::factory()->role('client')->create();

        foreach ([SondageStatus::EN_ATTENTE, SondageStatus::REFUSE, SondageStatus::EN_COURS, SondageStatus::TERMINE] as $statut) {
            $sondage = Sondage::factory()->for($owner)->statut($statut)->create();

            $this->actingAs($owner)->post(route('sondages.open', $sondage))->assertForbidden();
            $this->assertSame($statut, $sondage->fresh()->statut);
        }
    }

    public function test_a_survey_whose_end_date_has_passed_cannot_be_opened(): void
    {
        $owner = User::factory()->role('client')->create();
        $sondage = Sondage::factory()->for($owner)->statut(SondageStatus::VALIDE)
            ->create(['date_debut' => today()->subMonth(), 'date_fin' => today()->subDay()]);

        $this->actingAs($owner)->post(route('sondages.open', $sondage))->assertForbidden();
        $this->actingAs($owner)->get(route('sondages.show', $sondage))->assertSee('La date de fin est dépassée');

        $this->assertSame(SondageStatus::VALIDE, $sondage->fresh()->statut);
    }

    public function test_only_the_owner_can_open_a_survey(): void
    {
        $sondage = Sondage::factory()->statut(SondageStatus::VALIDE)->create();

        foreach (['client', 'administrateur'] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->post(route('sondages.open', $sondage))
                ->assertForbidden();
        }

        $this->assertSame(SondageStatus::VALIDE, $sondage->fresh()->statut);
    }

    public function test_owner_or_administrator_closes_an_open_survey(): void
    {
        $owner = User::factory()->role('client')->create();
        $parLeCreateur = Sondage::factory()->for($owner)->enCours()->create();
        $parUnAdmin = Sondage::factory()->for($owner)->enCours()->create();

        $this->actingAs($owner)->post(route('sondages.close', $parLeCreateur))->assertRedirect();
        $this->actingAs(User::factory()->role('administrateur')->create())
            ->post(route('sondages.close', $parUnAdmin))->assertRedirect();

        $this->assertSame(SondageStatus::TERMINE, $parLeCreateur->fresh()->statut);
        $this->assertSame(SondageStatus::TERMINE, $parUnAdmin->fresh()->statut);
    }

    public function test_other_users_cannot_close_a_survey_and_only_open_surveys_can_be_closed(): void
    {
        $owner = User::factory()->role('client')->create();
        $ouvert = Sondage::factory()->for($owner)->enCours()->create();
        $valide = Sondage::factory()->for($owner)->statut(SondageStatus::VALIDE)->create();

        $this->actingAs(User::factory()->role('client')->create())->post(route('sondages.close', $ouvert))->assertForbidden();
        $this->actingAs($owner)->post(route('sondages.close', $valide))->assertForbidden();

        $this->assertSame(SondageStatus::EN_COURS, $ouvert->fresh()->statut);
        $this->assertSame(SondageStatus::VALIDE, $valide->fresh()->statut);
    }

    public function test_complete_workflow_from_creation_to_results(): void
    {
        $client = User::factory()->role('client')->create();
        $admin = User::factory()->role('administrateur')->create();
        $participant = User::factory()->role('sondeur')->create();

        // 1. Création par le client
        $this->actingAs($client)->post(route('sondages.store'), [
            'titre' => 'Restaurant universitaire',
            'date_debut' => today()->format('Y-m-d'),
            'date_fin' => today()->addWeek()->format('Y-m-d'),
            'questions' => [
                ['title' => 'Qualité des repas', 'type_question' => 'multiple', 'options' => ['Bonne', 'Moyenne', 'Mauvaise']],
            ],
        ])->assertSessionHasNoErrors();
        $sondage = Sondage::sole();

        // Pas encore visible des participants
        $this->actingAs($participant)->get(route('participations.index'))->assertDontSee('Restaurant universitaire');

        // 2. Validation, 3. ouverture
        $this->actingAs($admin)->post(route('moderation.approve', $sondage))->assertRedirect();
        $this->actingAs($client)->post(route('sondages.open', $sondage))->assertRedirect();

        // 4. Participation
        $this->actingAs($participant)->get(route('participations.index'))->assertSee('Restaurant universitaire');
        $option = $sondage->questions()->sole()->options()->where('contenu', 'Moyenne')->sole();
        $this->actingAs($participant)->post(route('participations.store', $sondage), [
            'reponses' => [$option->question_id => $option->id],
        ])->assertSessionHasNoErrors();

        // 5. Clôture et résultats
        $this->actingAs($client)->post(route('sondages.close', $sondage))->assertRedirect();
        $this->actingAs($participant)->post(route('participations.store', $sondage), [
            'reponses' => [$option->question_id => $option->id],
        ])->assertRedirect();
        $this->assertSame(1, $sondage->participations()->count());

        $this->actingAs($client)->get(route('sondages.resultats', $sondage))
            ->assertOk()
            ->assertSee('Terminé')
            ->assertSeeInOrder(['Moyenne', '1 · 100,0 %']);
    }
}
